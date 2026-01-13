import 'package:flutter/material.dart';
import '../screens/auth/login_screen.dart';
import '../screens/dashboard/dashboard_screen.dart';
import '../screens/test/test_list_screen.dart';
import '../screens/test/instruction_screen.dart';
import '../screens/test/test_screen.dart';
import '../screens/test/result_screen.dart';

class Routes {
  static const login = '/';
  static const home = '/dashboard';
  static const dashboard = '/dashboard';
  static const tests = '/tests';
  static const instruction = '/instruction';
  static const test = '/test';
  static const result = '/result';

  static final all = <String, WidgetBuilder>{
    login: (_) => const LoginScreen(),
    dashboard: (_) => const DashboardScreen(),
    tests: (_) => const TestListScreen(),
    instruction: (_) => const InstructionScreen(),
    test: (_) => const TestScreen(),
    result: (_) => const ResultScreen(),
  };
}
