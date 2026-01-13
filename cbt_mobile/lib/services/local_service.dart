import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class LocalService {
  static const String _keyUserId = 'user_id';
  static const String _keyToken = 'auth_token';
  static const String _keyFirebaseUid = 'firebase_uid';
  static const String _keyUserData = 'user_data';

  static String? userId;
  static String? token;
  static String? firebaseUid;
  static Map<String, dynamic>? userData;

  // Initialize dari SharedPreferences saat app start
  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    userId = prefs.getString(_keyUserId);
    token = prefs.getString(_keyToken);
    firebaseUid = prefs.getString(_keyFirebaseUid);

    final userDataStr = prefs.getString(_keyUserData);
    if (userDataStr != null) {
      userData = jsonDecode(userDataStr);
    }
  }

  // Save user data setelah login
  static Future<void> saveUser({
    required String id,
    required String authToken,
    required String fbUid,
    Map<String, dynamic>? data,
  }) async {
    final prefs = await SharedPreferences.getInstance();

    userId = id;
    token = authToken;
    firebaseUid = fbUid;
    userData = data;

    await prefs.setString(_keyUserId, id);
    await prefs.setString(_keyToken, authToken);
    await prefs.setString(_keyFirebaseUid, fbUid);

    if (data != null) {
      await prefs.setString(_keyUserData, jsonEncode(data));
    }
  }

  // Update token
  static Future<void> updateToken(String newToken) async {
    final prefs = await SharedPreferences.getInstance();
    token = newToken;
    await prefs.setString(_keyToken, newToken);
  }

  // Update user data
  static Future<void> updateUserData(Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();
    userData = data;
    await prefs.setString(_keyUserData, jsonEncode(data));
  }

  // Check if user is logged in
  static bool get isLoggedIn => token != null && token!.isNotEmpty;

  // Clear all data saat logout
  static Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();

    userId = null;
    token = null;
    firebaseUid = null;
    userData = null;

    await prefs.remove(_keyUserId);
    await prefs.remove(_keyToken);
    await prefs.remove(_keyFirebaseUid);
    await prefs.remove(_keyUserData);
  }
}
