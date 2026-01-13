import '../services/api_service.dart';
import '../models/test_model.dart';
import '../models/question_model.dart';

class TestProvider {
  final _api = ApiService();

  Future<List<TestModel>> fetchTests() async {
    final data = await _api.getTests();
    return data.map((e) => TestModel.fromJson(e)).toList();
  }

  Future<List<QuestionModel>> fetchQuestions(String jadwalId) async {
    final data = await _api.getQuestions(jadwalId);
    return data.map((e) => QuestionModel.fromJson(e)).toList();
  }

  Future<Map<String, dynamic>> getTestDetail(String jadwalId) async {
    return await _api.getTestDetail(jadwalId);
  }

  Future<Map<String, dynamic>> startTest(String jadwalId) async {
    return await _api.startTest(jadwalId);
  }

  Future<Map<String, dynamic>> submitTest(String jadwalId) async {
    return await _api.submitTest(jadwalId);
  }

  Future<Map<String, dynamic>> getResult(String jadwalId) async {
    return await _api.getTestResult(jadwalId);
  }

  Future<List<dynamic>> getHistory() async {
    return await _api.getTestHistory();
  }
}
