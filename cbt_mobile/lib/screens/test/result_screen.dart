import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../models/test_model.dart';
import '../../core/routes.dart';

class ResultScreen extends StatefulWidget {
  const ResultScreen({super.key});

  @override
  State<ResultScreen> createState() => _ResultScreenState();
}

class _ResultScreenState extends State<ResultScreen> {
  final api = ApiService();

  TestModel? test;
  Map<String, dynamic>? result;
  bool loading = true;
  String? error;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments;
    if (args is TestModel && test == null) {
      test = args;
      _loadResult();
    } else if (args is Map && test == null) {
      test = TestModel.fromJson(args.cast<String, dynamic>());
      _loadResult();
    }
  }

  Future<void> _loadResult() async {
    if (test == null) return;

    setState(() {
      loading = true;
      error = null;
    });

    try {
      // Use pesertaTesId if available, otherwise use id_jadwal
      final idToUse = test!.pesertaTesId ?? test!.id;
      final usePesertaTesId = test!.pesertaTesId != null;

      print(
        'Loading result with ${usePesertaTesId ? "id_peserta_tes" : "id_jadwal"}: $idToUse',
      );

      final data = await api.getTestResult(
        idToUse,
        usePesertaTesId: usePesertaTesId,
      );
      if (mounted) {
        setState(() {
          result = data;
          loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          error = e.toString();
          loading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Hasil Ujian'),
        automaticallyImplyLeading: false,
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : error != null
          ? _buildError()
          : _buildContent(),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              'Gagal memuat hasil',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 8),
            Text(
              error!,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.grey),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _loadResult,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    final nilai = result?['nilai'] ?? 0;
    final benar = result?['jumlah_benar'] ?? 0;
    final salah = result?['jumlah_salah'] ?? 0;
    final total = result?['jumlah_soal'] ?? 0;
    final status = result?['status'] ?? 'selesai';

    final isLulus = nilai >= (result?['nilai_minimum'] ?? 70);

    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          // Result Card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                children: [
                  Icon(
                    isLulus ? Icons.emoji_events : Icons.sentiment_dissatisfied,
                    size: 80,
                    color: isLulus ? Colors.amber : Colors.grey,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    isLulus ? 'Selamat!' : 'Ujian Selesai',
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    test?.title ?? 'Ujian',
                    style: const TextStyle(fontSize: 16, color: Colors.grey),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Score Card
          Card(
            color: isLulus ? Colors.green.shade50 : Colors.orange.shade50,
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                children: [
                  const Text(
                    'NILAI',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Colors.grey,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    nilai.toString(),
                    style: TextStyle(
                      fontSize: 64,
                      fontWeight: FontWeight.bold,
                      color: isLulus ? Colors.green : Colors.orange,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: isLulus ? Colors.green : Colors.orange,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      isLulus ? 'LULUS' : 'TIDAK LULUS',
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Stats Card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  _buildStatRow(
                    Icons.quiz,
                    'Total Soal',
                    total.toString(),
                    Colors.blue,
                  ),
                  const Divider(),
                  _buildStatRow(
                    Icons.check_circle,
                    'Jawaban Benar',
                    benar.toString(),
                    Colors.green,
                  ),
                  const Divider(),
                  _buildStatRow(
                    Icons.cancel,
                    'Jawaban Salah',
                    salah.toString(),
                    Colors.red,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 32),

          // Action Buttons
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton.icon(
              onPressed: () => Navigator.pushNamedAndRemoveUntil(
                context,
                Routes.dashboard,
                (route) => false,
              ),
              icon: const Icon(Icons.home),
              label: const Text('Kembali ke Dashboard'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatRow(IconData icon, String label, String value, Color color) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, color: color),
          const SizedBox(width: 12),
          Text(label),
          const Spacer(),
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
        ],
      ),
    );
  }
}
