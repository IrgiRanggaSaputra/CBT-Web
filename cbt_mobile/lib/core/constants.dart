class AppConstants {
  // Base URL untuk API backend
  // Untuk development lokal (emulator Android): gunakan 10.0.2.2
  // Untuk development lokal (emulator iOS): gunakan localhost atau 127.0.0.1
  // Untuk device fisik: gunakan IP komputer di jaringan yang sama
  // Untuk production: gunakan domain/URL server production

  // Ubah sesuai environment Anda:
  static const String baseUrl = "https://cbtkiyoraka.web.id/api";
  // static const String baseUrl = "http://localhost/CBT_LPK_hosting/api";
  // static const String baseUrl = "http://192.168.1.xxx/CBT_LPK_hosting/api";
  // static const String baseUrl = "https://your-domain.com/api";

  // API Endpoints
  static const String authUrl = "$baseUrl/mobile_auth.php";
  static const String dashboardUrl = "$baseUrl/mobile_dashboard.php";
  static const String pesertaUrl = "$baseUrl/mobile_peserta.php";
  static const String testUrl = "$baseUrl/mobile_test.php";
  static const String jawabanUrl = "$baseUrl/mobile_jawaban.php";
  static const String hasilUrl = "$baseUrl/mobile_hasil.php";
  static const String linkFirebaseUrl = "$baseUrl/link_firebase.php";

  // Timeout settings
  static const Duration apiTimeout = Duration(seconds: 30);
}
