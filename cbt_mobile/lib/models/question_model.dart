class QuestionModel {
  final String id;
  final String question;
  final String a, b, c, d;
  final String? e; // Opsi kelima jika ada
  final String? gambar;
  final String? penjelasan;
  final String? jawabanBenar; // Untuk review setelah selesai
  final int? nomorUrut;
  String? userAnswer; // Jawaban user

  QuestionModel({
    required this.id,
    required this.question,
    required this.a,
    required this.b,
    required this.c,
    required this.d,
    this.e,
    this.gambar,
    this.penjelasan,
    this.jawabanBenar,
    this.nomorUrut,
    this.userAnswer,
  });

  factory QuestionModel.fromJson(Map<String, dynamic> json) {
    return QuestionModel(
      id: json['soal_id']?.toString() ?? json['id']?.toString() ?? '',
      question: json['pertanyaan'] ?? json['question'] ?? '',
      a: json['opsi_a'] ?? json['optionA'] ?? '',
      b: json['opsi_b'] ?? json['optionB'] ?? '',
      c: json['opsi_c'] ?? json['optionC'] ?? '',
      d: json['opsi_d'] ?? json['optionD'] ?? '',
      e: json['opsi_e'] ?? json['optionE'],
      gambar: json['gambar'],
      penjelasan: json['penjelasan'],
      jawabanBenar: json['jawaban_benar'] ?? json['correct_answer'],
      nomorUrut: int.tryParse(json['nomor_urut']?.toString() ?? ''),
      userAnswer: json['jawaban_peserta'] ?? json['user_answer'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'soal_id': id,
      'pertanyaan': question,
      'opsi_a': a,
      'opsi_b': b,
      'opsi_c': c,
      'opsi_d': d,
      'opsi_e': e,
      'gambar': gambar,
      'penjelasan': penjelasan,
      'jawaban_benar': jawabanBenar,
      'nomor_urut': nomorUrut,
      'jawaban_peserta': userAnswer,
    };
  }

  /// Get list of all options
  List<MapEntry<String, String>> get options {
    final opts = <MapEntry<String, String>>[
      MapEntry('A', a),
      MapEntry('B', b),
      MapEntry('C', c),
      MapEntry('D', d),
    ];
    if (e != null && e!.isNotEmpty) {
      opts.add(MapEntry('E', e!));
    }
    return opts;
  }

  /// Check apakah sudah dijawab
  bool get isAnswered => userAnswer != null && userAnswer!.isNotEmpty;

  /// Check apakah jawaban benar (untuk review)
  bool? get isCorrect {
    if (jawabanBenar == null || userAnswer == null) return null;
    return userAnswer!.toUpperCase() == jawabanBenar!.toUpperCase();
  }
}
