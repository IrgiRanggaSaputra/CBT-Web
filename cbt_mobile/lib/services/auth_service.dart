import 'package:firebase_auth/firebase_auth.dart';
import 'api_service.dart';
import 'local_service.dart';

class AuthService {
  final _auth = FirebaseAuth.instance;
  final _api = ApiService();

  /// Login dengan EMAIL dan PASSWORD (Firebase-first)
  /// 1. Login ke Firebase dengan email + password
  /// 2. Ambil data peserta dari backend berdasarkan email
  /// 3. Simpan data ke local storage
  Future<Map<String, dynamic>?> loginWithEmail(
    String email,
    String password,
  ) async {
    try {
      // Step 1: Login ke Firebase
      print('Step 1: Login ke Firebase...');
      final firebaseResult = await _auth.signInWithEmailAndPassword(
        email: email,
        password: password,
      );

      final firebaseUser = firebaseResult.user;
      if (firebaseUser == null) {
        throw Exception('Firebase login gagal');
      }

      final firebaseUid = firebaseUser.uid;
      print('Firebase login successful: $firebaseUid');

      // Step 2: Ambil data peserta dari backend berdasarkan email
      print('Step 2: Mengambil data peserta dari backend...');
      final pesertaData = await _api.getPesertaByEmail(email);
      print('Peserta data: $pesertaData');

      final pesertaId = pesertaData['id_peserta']?.toString() ?? '';

      // Build peserta data
      final peserta = <String, dynamic>{
        'id': pesertaId,
        'nama_peserta':
            pesertaData['nama_lengkap'] ?? pesertaData['nama_peserta'],
        'nomor_peserta': pesertaData['nomor_peserta'],
        'email': pesertaData['email'],
        'telepon': pesertaData['telepon'],
        'alamat': pesertaData['alamat'],
        'jenis_kelamin': pesertaData['jenis_kelamin'],
        'tanggal_lahir': pesertaData['tanggal_lahir'],
      };

      // Generate token dari peserta_id
      final token = pesertaData['token']?.toString() ?? 'firebase_$firebaseUid';

      // Step 3: Simpan ke local storage
      print('Step 3: Saving to local storage...');
      await LocalService.saveUser(
        id: pesertaId,
        authToken: token,
        fbUid: firebaseUid,
        data: peserta,
      );
      print('Saved to local storage');

      // Step 4: Link Firebase UID ke backend
      print('Step 4: Linking Firebase UID ke backend...');
      try {
        await _api.linkFirebaseUid(
          firebaseUid,
          pesertaId: int.tryParse(pesertaId),
        );
        print('Firebase UID linked successfully');
      } catch (e) {
        print('Warning: Failed to link Firebase UID: $e');
      }

      return {
        'peserta_id': pesertaId,
        'token': token,
        'firebase_uid': firebaseUid,
        'peserta': peserta,
      };
    } on FirebaseAuthException catch (e) {
      print('Firebase auth error: ${e.code} - ${e.message}');
      rethrow;
    } catch (e) {
      print('Login error: $e');
      rethrow;
    }
  }

  /// Register user baru ke Firebase (untuk admin)
  Future<Map<String, dynamic>?> registerWithEmail(
    String email,
    String password,
  ) async {
    try {
      print('Registering new Firebase user...');
      final result = await _auth.createUserWithEmailAndPassword(
        email: email,
        password: password,
      );

      final user = result.user;
      if (user == null) {
        throw Exception('Registrasi gagal');
      }

      print('Firebase register successful: ${user.uid}');
      return {'firebase_uid': user.uid, 'email': email};
    } on FirebaseAuthException catch (e) {
      print('Firebase register error: ${e.code} - ${e.message}');
      rethrow;
    }
  }

  /// Login dengan nomor peserta dan password (Legacy - Backend first)
  /// Tetap dipertahankan untuk kompatibilitas
  Future<Map<String, dynamic>?> login(
    String nomorPeserta,
    String password,
  ) async {
    try {
      // Step 1: Login ke backend
      print('Step 1: Login ke backend...');
      final loginResult = await _api.login(nomorPeserta, password);
      print('Backend login successful: $loginResult');

      final pesertaId =
          (loginResult['id_peserta'] ?? loginResult['peserta_id'])
              ?.toString() ??
          '';
      final token = loginResult['token']?.toString() ?? '';

      final peserta = <String, dynamic>{
        'id': pesertaId,
        'nama_peserta':
            loginResult['nama_lengkap'] ?? loginResult['nama_peserta'],
        'nomor_peserta': loginResult['nomor_peserta'],
        'email': loginResult['email'],
        'telepon': loginResult['telepon'],
        'alamat': loginResult['alamat'],
        'jenis_kelamin': loginResult['jenis_kelamin'],
        'tanggal_lahir': loginResult['tanggal_lahir'],
      };

      // Try Firebase login with generated email
      String firebaseUid = 'local_$pesertaId';
      final email = '${nomorPeserta}@cbt-lpk.local';

      try {
        final result = await _auth.signInWithEmailAndPassword(
          email: email,
          password: password,
        );
        firebaseUid = result.user?.uid ?? firebaseUid;
      } on FirebaseAuthException catch (e) {
        if (e.code == 'user-not-found') {
          try {
            final result = await _auth.createUserWithEmailAndPassword(
              email: email,
              password: password,
            );
            firebaseUid = result.user?.uid ?? firebaseUid;
          } catch (_) {}
        }
      } catch (_) {}

      await LocalService.saveUser(
        id: pesertaId,
        authToken: token,
        fbUid: firebaseUid,
        data: peserta,
      );

      if (!firebaseUid.startsWith('local_')) {
        try {
          await _api.linkFirebaseUid(firebaseUid);
        } catch (_) {}
      }

      return {
        'peserta_id': pesertaId,
        'token': token,
        'firebase_uid': firebaseUid,
        'peserta': peserta,
      };
    } catch (e) {
      print('Login error: $e');
      return null;
    }
  }

  /// Logout dari Firebase dan backend
  Future<void> logout() async {
    try {
      // Logout dari backend
      await _api.logout();
    } catch (e) {
      print('Backend logout error: $e');
    }

    // Logout dari Firebase
    await _auth.signOut();

    // Clear local data
    await LocalService.clear();

    print('Logged out successfully');
  }

  /// Check apakah user masih login (token valid)
  Future<bool> isLoggedIn() async {
    if (!LocalService.isLoggedIn) {
      return false;
    }

    try {
      await _api.verifyToken();
      return true;
    } catch (e) {
      print('Token invalid: $e');
      // Token invalid, clear local data
      await LocalService.clear();
      return false;
    }
  }

  /// Get current Firebase user
  User? get currentUser => _auth.currentUser;

  /// Get Firebase UID
  String? get firebaseUid => _auth.currentUser?.uid ?? LocalService.firebaseUid;
}
