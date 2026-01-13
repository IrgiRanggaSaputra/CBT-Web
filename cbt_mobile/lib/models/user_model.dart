class UserModel {
  final String id;
  final String name;
  final String number;
  final String? email;
  final String? phone;
  final String? alamat;
  final String? tanggalLahir;
  final String? jenisKelamin;
  final String? foto;

  UserModel({
    required this.id,
    required this.name,
    required this.number,
    this.email,
    this.phone,
    this.alamat,
    this.tanggalLahir,
    this.jenisKelamin,
    this.foto,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id']?.toString() ?? json['peserta_id']?.toString() ?? '',
      name: json['nama_peserta'] ?? json['name'] ?? '',
      number: json['nomor_peserta'] ?? json['participantNumber'] ?? '',
      email: json['email'],
      phone: json['telepon'] ?? json['phone'],
      alamat: json['alamat'],
      tanggalLahir: json['tanggal_lahir'],
      jenisKelamin: json['jenis_kelamin'],
      foto: json['foto'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nama_peserta': name,
      'nomor_peserta': number,
      'email': email,
      'telepon': phone,
      'alamat': alamat,
      'tanggal_lahir': tanggalLahir,
      'jenis_kelamin': jenisKelamin,
      'foto': foto,
    };
  }
}
