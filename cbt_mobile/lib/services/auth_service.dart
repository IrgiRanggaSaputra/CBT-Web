import 'package:firebase_auth/firebase_auth.dart';
import 'api_service.dart';
import 'local_service.dart';

class AuthService {
  final _auth = FirebaseAuth.instance;
  final _api = ApiService();

  /// Login dengan nomor peserta dan password
  /// 1. Login ke backend untuk validasi credential
  /// 2. Jika sukses, login/register ke Firebase untuk mendapat UID
  /// 3. Link Firebase UID ke akun peserta di backend
  Future<Map<String, dynamic>?> login(
    String nomorPeserta,
    String password,
  ) async {
    try {
      // Step 1: Login ke backend
      print('Step 1: Login ke backend...');
      final loginResult = await _api.login(nomorPeserta, password);
      print('Backend login successful: $loginResult');

      // Extract data dari response - API returns data directly
      // Response: {id_peserta, nomor_peserta, nama_lengkap, email, ..., token}
      final pesertaId =
          (loginResult['id_peserta'] ?? loginResult['peserta_id'])
              ?.toString() ??
          '';
      final token = loginResult['token']?.toString() ?? '';

      // Build peserta data from loginResult
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

      // Step 2: Login/Register ke Firebase
      print('Step 2: Login/Register ke Firebase...');
      final email = '${nomorPeserta}@cbt-lpk.local';
      User? firebaseUser;
      String firebaseUid;

      try {
        // Coba login dulu
        final result = await _auth.signInWithEmailAndPassword(
          email: email,
          password: password,
        );
        firebaseUser = result.user;
        firebaseUid = firebaseUser?.uid ?? '';
        print('Firebase login successful: $firebaseUid');
      } on FirebaseAuthException catch (e) {
        if (e.code == 'user-not-found') {
          // User belum ada, register dulu
          print('Firebase user not found, creating new user...');
          try {
            final result = await _auth.createUserWithEmailAndPassword(
              email: email,
              password: password,
            );
            firebaseUser = result.user;
            firebaseUid = firebaseUser?.uid ?? '';
            print('Firebase register successful: $firebaseUid');
          } catch (regError) {
            print('Firebase register error: $regError');
            // Fallback: use pesertaId as firebaseUid
            firebaseUid = 'local_$pesertaId';
          }
        } else {
          print('Firebase auth error: ${e.code} - ${e.message}');
          // Fallback: use pesertaId as firebaseUid
          firebaseUid = 'local_$pesertaId';
        }
      } catch (e) {
        print('Firebase error: $e');
        // Fallback: use pesertaId as firebaseUid
        firebaseUid = 'local_$pesertaId';
      }

      // Simpan ke local storage
      print('Step 3: Saving to local storage...');
      await LocalService.saveUser(
        id: pesertaId,
        authToken: token,
        fbUid: firebaseUid,
        data: peserta,
      );
      print('Saved to local storage');

      // Step 4: Link Firebase UID ke backend (opsional)
      if (firebaseUid.isNotEmpty && !firebaseUid.startsWith('local_')) {
        print('Step 4: Linking Firebase UID ke backend...');
        try {
          await _api.linkFirebaseUid(firebaseUid);
          print('Firebase UID linked successfully');
        } catch (e) {
          // Link gagal tidak fatal
          print('Warning: Failed to link Firebase UID: $e');
        }
      }

      // Return data untuk UI
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
