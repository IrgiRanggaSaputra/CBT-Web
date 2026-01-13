class TestModel {
  final String id;
  final String title;
  final String? description;
  final int? jumlahSoal;
  final int? durasi; // dalam menit
  final String? status;
  final String? tanggalMulai;
  final String? tanggalSelesai;
  final String? waktuMulai;
  final String? waktuSelesai;
  final bool? isStarted;
  final bool? isFinished;
  final int? nilai;

  TestModel({
    required this.id,
    required this.title,
    this.description,
    this.jumlahSoal,
    this.durasi,
    this.status,
    this.tanggalMulai,
    this.tanggalSelesai,
    this.waktuMulai,
    this.waktuSelesai,
    this.isStarted,
    this.isFinished,
    this.nilai,
  });

  factory TestModel.fromJson(Map<String, dynamic> json) {
    return TestModel(
      id: json['jadwal_id']?.toString() ?? json['id']?.toString() ?? '',
      title: json['nama_tes'] ?? json['title'] ?? '',
      description: json['deskripsi'] ?? json['description'],
      jumlahSoal: int.tryParse(json['jumlah_soal']?.toString() ?? ''),
      durasi: int.tryParse(json['durasi']?.toString() ?? ''),
      status: json['status'],
      tanggalMulai: json['tanggal_mulai'],
      tanggalSelesai: json['tanggal_selesai'],
      waktuMulai: json['waktu_mulai'],
      waktuSelesai: json['waktu_selesai'],
      isStarted: json['is_started'] == true || json['is_started'] == 1,
      isFinished: json['is_finished'] == true || json['is_finished'] == 1,
      nilai: int.tryParse(json['nilai']?.toString() ?? ''),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'jadwal_id': id,
      'nama_tes': title,
      'deskripsi': description,
      'jumlah_soal': jumlahSoal,
      'durasi': durasi,
      'status': status,
      'tanggal_mulai': tanggalMulai,
      'tanggal_selesai': tanggalSelesai,
      'waktu_mulai': waktuMulai,
      'waktu_selesai': waktuSelesai,
      'is_started': isStarted,
      'is_finished': isFinished,
      'nilai': nilai,
    };
  }

  /// Check apakah tes bisa dimulai
  bool get canStart {
    if (isFinished == true) return false;
    if (status == 'selesai') return false;
    return true;
  }

  /// Get status display text
  String get statusText {
    if (isFinished == true || status == 'selesai') return 'Selesai';
    if (isStarted == true || status == 'berlangsung')
      return 'Sedang Berlangsung';
    return 'Belum Dimulai';
  }
}
