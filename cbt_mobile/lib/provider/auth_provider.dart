import '../services/auth_service.dart';
import '../services/local_service.dart';

class AuthProvider {
  final _service = AuthService();

  /// Login dengan nomor peserta dan password
  Future<bool> login(String nomorPeserta, String password) async {
    final result = await _service.login(nomorPeserta, password);
    return result != null;
  }

  /// Logout
  Future<void> logout() async {
    await _service.logout();
  }

  /// Check if user is logged in
  bool get isLoggedIn => LocalService.isLoggedIn;

  /// Check if token is still valid
  Future<bool> isAuthenticated() async {
    return await _service.isLoggedIn();
  }

  /// Get current user ID
  String? get userId => LocalService.userId;

  /// Get current Firebase UID
  String? get firebaseUid => _service.firebaseUid;
}
