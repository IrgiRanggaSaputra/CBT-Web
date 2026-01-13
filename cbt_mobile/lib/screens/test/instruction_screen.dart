import 'package:flutter/material.dart';
import '../../core/routes.dart';
import '../../models/test_model.dart';
import '../../services/api_service.dart';

class InstructionScreen extends StatefulWidget {
  const InstructionScreen({super.key});

  @override
  State<InstructionScreen> createState() => _InstructionScreenState();
}

class _InstructionScreenState extends State<InstructionScreen> {
  final api = ApiService();

  TestModel? test;
  Map<String, dynamic>? detail;
  bool loading = true;
  bool starting = false;
  String? error;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments;
    if (args is TestModel) {
      test = args;
      _loadDetail();
    } else if (args is Map) {
      test = TestModel.fromJson(args.cast<String, dynamic>());
      _loadDetail();
    }
  }

  Future<void> _loadDetail() async {
    if (test == null) return;

    setState(() {
      loading = true;
      error = null;
    });

    try {
      final data = await api.getTestDetail(test!.id);
      if (mounted) {
        setState(() {
          detail = data;
          // Update test with more details
          if (data['test'] != null) {
            test = TestModel.fromJson(data['test']);
          }
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
      appBar: AppBar(title: Text(test?.title ?? 'Instruksi Ujian')),
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
              'Gagal memuat detail ujian',
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
              onPressed: _loadDetail,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    final petunjuk =
        detail?['petunjuk'] ?? 'Silakan baca instruksi sebelum memulai ujian.';
    final canStart = test?.canStart ?? true;

    return SingleChildScrollView(
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Test Info Card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  const Icon(Icons.assignment, size: 64, color: Colors.blue),
                  const SizedBox(height: 16),
                  Text(
                    test?.title ?? 'Ujian',
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  if (test?.description != null) ...[
                    const SizedBox(height: 8),
                    Text(
                      test!.description!,
                      style: const TextStyle(color: Colors.grey),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Info Details
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  _buildInfoRow(
                    Icons.quiz,
                    'Jumlah Soal',
                    '${test?.jumlahSoal ?? '-'} soal',
                  ),
                  const Divider(),
                  _buildInfoRow(
                    Icons.timer,
                    'Durasi',
                    '${test?.durasi ?? '-'} menit',
                  ),
                  if (test?.tanggalMulai != null) ...[
                    const Divider(),
                    _buildInfoRow(
                      Icons.calendar_today,
                      'Tanggal Mulai',
                      test!.tanggalMulai!,
                    ),
                  ],
                  if (test?.tanggalSelesai != null) ...[
                    const Divider(),
                    _buildInfoRow(
                      Icons.event,
                      'Tanggal Selesai',
                      test!.tanggalSelesai!,
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Instructions
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.info, color: Colors.blue),
                      SizedBox(width: 8),
                      Text(
                        'Petunjuk Pengerjaan',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(petunjuk, style: const TextStyle(height: 1.5)),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Start Button
          if (canStart)
            SizedBox(
              height: 50,
              child: starting
                  ? const Center(child: CircularProgressIndicator())
                  : ElevatedButton.icon(
                      onPressed: _startTest,
                      icon: const Icon(Icons.play_arrow),
                      label: Text(
                        test?.isStarted == true
                            ? 'Lanjutkan Ujian'
                            : 'Mulai Ujian',
                        style: const TextStyle(fontSize: 16),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.green,
                        foregroundColor: Colors.white,
                      ),
                    ),
            )
          else if (test?.isFinished == true)
            SizedBox(
              height: 50,
              child: ElevatedButton.icon(
                onPressed: () => Navigator.pushNamed(
                  context,
                  Routes.result,
                  arguments: test,
                ),
                icon: const Icon(Icons.visibility),
                label: const Text(
                  'Lihat Hasil',
                  style: TextStyle(fontSize: 16),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Icon(icon, size: 20, color: Colors.grey),
          const SizedBox(width: 12),
          Text(label, style: const TextStyle(color: Colors.grey)),
          const Spacer(),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Future<void> _startTest() async {
    if (test == null) return;

    setState(() => starting = true);

    try {
      await api.startTest(test!.id);

      if (!mounted) return;

      Navigator.pushReplacementNamed(context, Routes.test, arguments: test);
    } catch (e) {
      if (!mounted) return;

      setState(() => starting = false);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal memulai ujian: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
