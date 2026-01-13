import 'dart:async';
import 'package:flutter/material.dart';
import '../../core/routes.dart';
import '../../models/test_model.dart';
import '../../models/question_model.dart';
import '../../services/api_service.dart';

class TestScreen extends StatefulWidget {
  const TestScreen({super.key});

  @override
  State<TestScreen> createState() => _TestScreenState();
}

class _TestScreenState extends State<TestScreen> {
  final api = ApiService();

  TestModel? test;
  List<QuestionModel> questions = [];
  List<dynamic>? initialSoal; // Soal dari startTest response
  int currentIndex = 0;
  Map<String, String> answers = {}; // soal_id -> jawaban

  bool loading = true;
  bool submitting = false;
  String? error;

  Timer? _timer;
  int _remainingSeconds = 0;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final args = ModalRoute.of(context)?.settings.arguments;

    if (test != null) return; // Already initialized

    if (args is Map) {
      // New format: {test: TestModel, soal: List}
      if (args.containsKey('test')) {
        final testData = args['test'];
        if (testData is TestModel) {
          test = testData;
        } else if (testData is Map) {
          test = TestModel.fromJson(testData.cast<String, dynamic>());
        }
        initialSoal = args['soal'] as List?;
      } else {
        // Old format: just a Map representing TestModel
        test = TestModel.fromJson(args.cast<String, dynamic>());
      }
      _loadQuestions();
    } else if (args is TestModel) {
      test = args;
      _loadQuestions();
    }
  }

  Future<void> _loadQuestions() async {
    if (test == null || test!.pesertaTesId == null) {
      setState(() {
        error = 'Data tes tidak valid. pesertaTesId tidak ditemukan.';
        loading = false;
      });
      return;
    }

    setState(() {
      loading = true;
      error = null;
    });

    try {
      List<dynamic> soalList = [];
      Map<String, dynamic> jawabanTersimpan = {};

      // Gunakan soal dari startTest response
      if (initialSoal != null && initialSoal!.isNotEmpty) {
        print('Using ${initialSoal!.length} questions from startTest response');
        soalList = initialSoal!;
      } else {
        // Fallback: coba ambil soal dari API getQuestions
        print('No questions from startTest, trying API...');
        try {
          final response = await api.getQuestions(test!.pesertaTesId!);
          soalList = response['soal'] as List? ?? [];
          jawabanTersimpan =
              response['jawaban_tersimpan'] as Map<String, dynamic>? ?? {};
          print('Got ${soalList.length} questions from API');
        } catch (apiError) {
          print('API getQuestions error: $apiError');
          // Jika API gagal, tampilkan pesan yang lebih jelas
          if (initialSoal == null || initialSoal!.isEmpty) {
            throw Exception(
              'Gagal memuat soal. Pastikan tes ini memiliki soal yang sudah dikonfigurasi.',
            );
          }
        }
      }

      if (mounted) {
        setState(() {
          questions = soalList
              .map((e) => QuestionModel.fromJson(e as Map<String, dynamic>))
              .toList();

          // Load saved answers
          jawabanTersimpan.forEach((key, value) {
            // key adalah id_soal_tes, value adalah jawaban
            final questionIndex = questions.indexWhere(
              (q) => q.soalTesId == key || q.id == key,
            );
            if (questionIndex >= 0) {
              answers[questions[questionIndex].id] = value.toString();
            }
          });

          loading = false;

          // Check if no questions
          if (questions.isEmpty) {
            error =
                'Tidak ada soal untuk tes ini. Silakan hubungi administrator.';
          }

          // Start timer
          if (test?.durasi != null && questions.isNotEmpty) {
            _remainingSeconds = test!.durasi! * 60;
            _startTimer();
          }
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

  void _startTimer() {
    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_remainingSeconds > 0) {
        setState(() {
          _remainingSeconds--;
        });
      } else {
        _timer?.cancel();
        _showTimeUpDialog();
      }
    });
  }

  void _showTimeUpDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Text('Waktu Habis'),
        content: const Text(
          'Waktu pengerjaan telah habis. Jawaban akan dikirim otomatis.',
        ),
        actions: [
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _submitTest();
            },
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  String get _timerText {
    final hours = _remainingSeconds ~/ 3600;
    final minutes = (_remainingSeconds % 3600) ~/ 60;
    final seconds = _remainingSeconds % 60;

    if (hours > 0) {
      return '${hours.toString().padLeft(2, '0')}:${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
    }
    return '${minutes.toString().padLeft(2, '0')}:${seconds.toString().padLeft(2, '0')}';
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: _onWillPop,
      child: Scaffold(
        appBar: AppBar(
          title: Text(test?.title ?? 'Ujian'),
          automaticallyImplyLeading: false,
          actions: [
            if (_remainingSeconds > 0)
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                padding: const EdgeInsets.symmetric(horizontal: 12),
                decoration: BoxDecoration(
                  color: _remainingSeconds < 300 ? Colors.red : Colors.blue,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Center(
                  child: Text(
                    _timerText,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
          ],
        ),
        body: loading
            ? const Center(child: CircularProgressIndicator())
            : error != null
            ? _buildError()
            : questions.isEmpty
            ? _buildEmpty()
            : _buildContent(),
        bottomNavigationBar: !loading && error == null && questions.isNotEmpty
            ? _buildBottomBar()
            : null,
      ),
    );
  }

  Future<bool> _onWillPop() async {
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Keluar dari Ujian?'),
        content: const Text(
          'Jawaban yang sudah diisi akan tersimpan. Anda bisa melanjutkan nanti.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
    return result ?? false;
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
              'Gagal memuat soal',
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
              onPressed: _loadQuestions,
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return const Center(
      child: Text(
        'Tidak ada soal tersedia',
        style: TextStyle(fontSize: 18, color: Colors.grey),
      ),
    );
  }

  Widget _buildContent() {
    final question = questions[currentIndex];

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Progress indicator
          LinearProgressIndicator(
            value: (currentIndex + 1) / questions.length,
            backgroundColor: Colors.grey.shade200,
          ),
          const SizedBox(height: 8),
          Text(
            'Soal ${currentIndex + 1} dari ${questions.length}',
            style: const TextStyle(color: Colors.grey),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),

          // Question Card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    question.question,
                    style: const TextStyle(fontSize: 16, height: 1.5),
                  ),
                  if (question.gambar != null &&
                      question.gambar!.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.network(
                        question.gambar!,
                        fit: BoxFit.contain,
                        errorBuilder: (_, __, ___) => Container(
                          padding: const EdgeInsets.all(16),
                          color: Colors.grey.shade200,
                          child: const Text('Gambar tidak dapat dimuat'),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Options
          ...question.options.map(
            (option) => _buildOption(question.id, option.key, option.value),
          ),
        ],
      ),
    );
  }

  Widget _buildOption(String questionId, String optionKey, String optionValue) {
    final isSelected = answers[questionId] == optionKey;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      color: isSelected ? Colors.blue.shade50 : null,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: BorderSide(
          color: isSelected ? Colors.blue : Colors.grey.shade300,
          width: isSelected ? 2 : 1,
        ),
      ),
      child: InkWell(
        onTap: () => _selectAnswer(questionId, optionKey),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: isSelected ? Colors.blue : Colors.grey.shade200,
                ),
                child: Center(
                  child: Text(
                    optionKey,
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: isSelected ? Colors.white : Colors.black,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  optionValue,
                  style: TextStyle(
                    color: isSelected ? Colors.blue.shade900 : null,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _selectAnswer(String questionId, String answer) async {
    setState(() {
      answers[questionId] = answer;
    });

    // Auto-save answer
    try {
      // Cari id_soal_tes dari question yang sedang dijawab
      final question = questions.firstWhere(
        (q) => q.id == questionId,
        orElse: () => questions[currentIndex],
      );

      await api.saveAnswer(
        pesertaTesId: test!.pesertaTesId!,
        soalTesId: question.soalTesId ?? questionId,
        jawaban: answer,
      );
    } catch (e) {
      print('Error saving answer: $e');
      // Continue anyway, will retry on submit
    }
  }

  Widget _buildBottomBar() {
    final answeredCount = answers.length;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Question navigation
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: currentIndex > 0 ? _prevQuestion : null,
                    child: const Text('Sebelumnya'),
                  ),
                ),
                const SizedBox(width: 8),
                // Question number dropdown
                PopupMenuButton<int>(
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 12,
                    ),
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.grey),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text('${currentIndex + 1}/${questions.length}'),
                  ),
                  itemBuilder: (context) =>
                      List.generate(questions.length, (index) {
                        final q = questions[index];
                        final isAnswered = answers.containsKey(q.id);
                        return PopupMenuItem(
                          value: index,
                          child: Row(
                            children: [
                              Container(
                                width: 24,
                                height: 24,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: isAnswered
                                      ? Colors.green
                                      : Colors.grey.shade300,
                                ),
                                child: Center(
                                  child: Text(
                                    '${index + 1}',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isAnswered
                                          ? Colors.white
                                          : Colors.black,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Text(isAnswered ? 'Terjawab' : 'Belum dijawab'),
                            ],
                          ),
                        );
                      }),
                  onSelected: (index) {
                    setState(() {
                      currentIndex = index;
                    });
                  },
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: currentIndex < questions.length - 1
                      ? ElevatedButton(
                          onPressed: _nextQuestion,
                          child: const Text('Selanjutnya'),
                        )
                      : ElevatedButton(
                          onPressed: _confirmSubmit,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.green,
                          ),
                          child: const Text('Selesai'),
                        ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              '$answeredCount dari ${questions.length} soal terjawab',
              style: const TextStyle(color: Colors.grey, fontSize: 12),
            ),
          ],
        ),
      ),
    );
  }

  void _prevQuestion() {
    if (currentIndex > 0) {
      setState(() {
        currentIndex--;
      });
    }
  }

  void _nextQuestion() {
    if (currentIndex < questions.length - 1) {
      setState(() {
        currentIndex++;
      });
    }
  }

  void _confirmSubmit() {
    final unanswered = questions.length - answers.length;

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Selesaikan Ujian?'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Soal terjawab: ${answers.length}'),
            if (unanswered > 0)
              Text(
                'Soal belum dijawab: $unanswered',
                style: const TextStyle(color: Colors.red),
              ),
            const SizedBox(height: 8),
            const Text('Apakah Anda yakin ingin menyelesaikan ujian?'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Kembali'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _submitTest();
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.green),
            child: const Text('Selesai'),
          ),
        ],
      ),
    );
  }

  Future<void> _submitTest() async {
    if (test == null || test!.pesertaTesId == null) return;

    setState(() => submitting = true);

    try {
      // Submit test
      await api.submitTest(test!.pesertaTesId!);

      _timer?.cancel();

      if (!mounted) return;

      Navigator.pushReplacementNamed(context, Routes.result, arguments: test);
    } catch (e) {
      if (!mounted) return;

      setState(() => submitting = false);

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal mengirim jawaban: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
