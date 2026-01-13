import 'dart:convert';
import 'package:http/http.dart' as http;
import '../core/constants.dart';
import 'local_service.dart';

class ApiService {
  // Helper untuk membuat headers dengan token
  Map<String, String> _getHeaders({bool withAuth = true}) {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (withAuth && LocalService.token != null) {
      headers['Authorization'] = 'Bearer ${LocalService.token}';
    }

    return headers;
  }

  // Helper untuk handle API response
  dynamic _handleResponse(http.Response response) {
    print('API Status Code: ${response.statusCode}');
    print('API Response Body: ${response.body}');

    final decoded = jsonDecode(response.body);

    if (response.statusCode == 200) {
      if (decoded['status'] == 'success') {
        final data = decoded['data'];
        // Ensure we return a Map, not null
        if (data == null) {
          return <String, dynamic>{};
        }
        return data;
      } else {
        throw Exception(decoded['message'] ?? 'Unknown error');
      }
    } else {
      throw Exception(
        decoded['message'] ?? 'API Error: ${response.statusCode}',
      );
    }
  }

  // ==================== AUTH ====================

  /// Login dengan nomor peserta dan password
  Future<Map<String, dynamic>> login(
    String nomorPeserta,
    String password,
  ) async {
    final url = "${AppConstants.authUrl}?action=login";
    print('API Call: $url');

    try {
      final res = await http
          .post(
            Uri.parse(url),
            headers: _getHeaders(withAuth: false),
            body: jsonEncode({
              'nomor_peserta': nomorPeserta,
              'password': password,
            }),
          )
          .timeout(AppConstants.apiTimeout);

      final result = _handleResponse(res);
      // Ensure result is a Map
      if (result is Map<String, dynamic>) {
        return result;
      } else if (result is Map) {
        return Map<String, dynamic>.from(result);
      } else {
        return <String, dynamic>{};
      }
    } catch (e) {
      print('ERROR in login: $e');
      rethrow;
    }
  }

  /// Logout
  Future<void> logout() async {
    final url = "${AppConstants.authUrl}?action=logout";
    print('API Call: $url');

    try {
      await http
          .post(
            Uri.parse(url),
            headers: _getHeaders(),
            body: jsonEncode({'token': LocalService.token}),
          )
          .timeout(AppConstants.apiTimeout);
    } catch (e) {
      print('ERROR in logout: $e');
      // Tetap clear local data meskipun API error
    }
  }

  /// Verify token
  Future<Map<String, dynamic>> verifyToken() async {
    final url = "${AppConstants.authUrl}?action=verify-token";
    print('API Call: $url');

    try {
      final res = await http
          .post(
            Uri.parse(url),
            headers: _getHeaders(),
            body: jsonEncode({'token': LocalService.token}),
          )
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in verifyToken: $e');
      rethrow;
    }
  }

  /// Link Firebase UID ke akun peserta
  Future<Map<String, dynamic>> linkFirebaseUid(String firebaseUid) async {
    final url = AppConstants.linkFirebaseUrl;
    print('API Call: $url');

    try {
      final res = await http
          .post(
            Uri.parse(url),
            headers: _getHeaders(),
            body: jsonEncode({
              'firebase_uid': firebaseUid,
              'peserta_id': LocalService.userId,
            }),
          )
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in linkFirebaseUid: $e');
      rethrow;
    }
  }

  // ==================== DASHBOARD ====================

  /// Get dashboard data
  Future<Map<String, dynamic>> getDashboard() async {
    // Gunakan peserta_id langsung, lebih reliable daripada firebase_uid
    final url =
        "${AppConstants.dashboardUrl}?peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .get(Uri.parse(url), headers: _getHeaders())
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in getDashboard: $e');
      rethrow;
    }
  }

  // ==================== PESERTA / PROFILE ====================

  /// Get profile peserta
  Future<Map<String, dynamic>> getProfile() async {
    final url =
        "${AppConstants.pesertaUrl}?action=get&peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .get(Uri.parse(url), headers: _getHeaders())
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in getProfile: $e');
      rethrow;
    }
  }

  /// Update profile peserta
  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> data) async {
    final url = "${AppConstants.pesertaUrl}?action=update";
    print('API Call: $url');

    try {
      final body = {'peserta_id': LocalService.userId, ...data};

      final res = await http
          .put(Uri.parse(url), headers: _getHeaders(), body: jsonEncode(body))
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in updateProfile: $e');
      rethrow;
    }
  }

  /// Change password
  Future<Map<String, dynamic>> changePassword(
    String oldPassword,
    String newPassword,
  ) async {
    final url =
        "${AppConstants.pesertaUrl}?action=change-password&peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .put(
            Uri.parse(url),
            headers: _getHeaders(),
            body: jsonEncode({
              'peserta_id': LocalService.userId,
              'old_password': oldPassword,
              'new_password': newPassword,
            }),
          )
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in changePassword: $e');
      rethrow;
    }
  }

  // ==================== TESTS ====================

  /// Get daftar tes yang tersedia
  Future<List<dynamic>> getTests() async {
    final url =
        "${AppConstants.testUrl}?action=list&peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .get(Uri.parse(url), headers: _getHeaders())
          .timeout(AppConstants.apiTimeout);

      final data = _handleResponse(res);
      return data['tests'] ?? [];
    } catch (e) {
      print('ERROR in getTests: $e');
      rethrow;
    }
  }

  /// Get detail tes
  Future<Map<String, dynamic>> getTestDetail(String jadwalId) async {
    final url =
        "${AppConstants.testUrl}?action=detail&peserta_id=${LocalService.userId}&jadwal_id=$jadwalId";
    print('API Call: $url');

    try {
      final res = await http
          .get(Uri.parse(url), headers: _getHeaders())
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in getTestDetail: $e');
      rethrow;
    }
  }

  /// Start test / mulai mengerjakan
  Future<Map<String, dynamic>> startTest(String jadwalId) async {
    final url =
        "${AppConstants.testUrl}?action=start&peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .post(
            Uri.parse(url),
            headers: _getHeaders(),
            body: jsonEncode({
              'peserta_id': LocalService.userId,
              'jadwal_id': jadwalId,
            }),
          )
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in startTest: $e');
      rethrow;
    }
  }

  /// Get semua soal untuk tes
  Future<List<dynamic>> getQuestions(String jadwalId) async {
    final url =
        "${AppConstants.testUrl}?action=questions&peserta_id=${LocalService.userId}&jadwal_id=$jadwalId";
    print('API Call: $url');

    try {
      final res = await http
          .get(Uri.parse(url), headers: _getHeaders())
          .timeout(AppConstants.apiTimeout);

      final data = _handleResponse(res);
      return data['questions'] ?? [];
    } catch (e) {
      print('ERROR in getQuestions: $e');
      rethrow;
    }
  }

  // ==================== JAWABAN ====================

  /// Save jawaban satu soal
  Future<Map<String, dynamic>> saveAnswer({
    required String jadwalId,
    required String soalId,
    required String jawaban,
  }) async {
    final url =
        "${AppConstants.jawabanUrl}?action=save&peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .post(
            Uri.parse(url),
            headers: _getHeaders(),
            body: jsonEncode({
              'peserta_id': LocalService.userId,
              'jadwal_id': jadwalId,
              'soal_id': soalId,
              'jawaban': jawaban,
            }),
          )
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in saveAnswer: $e');
      rethrow;
    }
  }

  /// Save jawaban batch (multiple)
  Future<Map<String, dynamic>> saveAnswerBatch({
    required String jadwalId,
    required List<Map<String, dynamic>> answers,
  }) async {
    final url =
        "${AppConstants.jawabanUrl}?action=save-batch&peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .post(
            Uri.parse(url),
            headers: _getHeaders(),
            body: jsonEncode({
              'peserta_id': LocalService.userId,
              'jadwal_id': jadwalId,
              'answers': answers,
            }),
          )
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in saveAnswerBatch: $e');
      rethrow;
    }
  }

  /// Submit test / selesaikan tes
  Future<Map<String, dynamic>> submitTest(String jadwalId) async {
    final url =
        "${AppConstants.jawabanUrl}?action=submit&peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .post(
            Uri.parse(url),
            headers: _getHeaders(),
            body: jsonEncode({
              'peserta_id': LocalService.userId,
              'jadwal_id': jadwalId,
            }),
          )
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in submitTest: $e');
      rethrow;
    }
  }

  // ==================== HASIL ====================

  /// Get hasil tes
  Future<Map<String, dynamic>> getTestResult(String jadwalId) async {
    final url =
        "${AppConstants.hasilUrl}?action=get&peserta_id=${LocalService.userId}&jadwal_id=$jadwalId";
    print('API Call: $url');

    try {
      final res = await http
          .get(Uri.parse(url), headers: _getHeaders())
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in getTestResult: $e');
      rethrow;
    }
  }

  /// Get detail hasil tes (dengan jawaban)
  Future<Map<String, dynamic>> getTestResultDetail(String jadwalId) async {
    final url =
        "${AppConstants.hasilUrl}?action=detail&peserta_id=${LocalService.userId}&jadwal_id=$jadwalId";
    print('API Call: $url');

    try {
      final res = await http
          .get(Uri.parse(url), headers: _getHeaders())
          .timeout(AppConstants.apiTimeout);

      return _handleResponse(res);
    } catch (e) {
      print('ERROR in getTestResultDetail: $e');
      rethrow;
    }
  }

  /// Get riwayat tes
  Future<List<dynamic>> getTestHistory() async {
    final url =
        "${AppConstants.hasilUrl}?action=history&peserta_id=${LocalService.userId}";
    print('API Call: $url');

    try {
      final res = await http
          .get(Uri.parse(url), headers: _getHeaders())
          .timeout(AppConstants.apiTimeout);

      final data = _handleResponse(res);
      return data['history'] ?? [];
    } catch (e) {
      print('ERROR in getTestHistory: $e');
      rethrow;
    }
  }
}
