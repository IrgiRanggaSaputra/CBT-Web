# 🚀 IMPLEMENTATION ROADMAP

**Project:** CBT LPK - Web + Mobile App  
**Focus:** Mobile App Development  
**Timeline:** 8 Weeks (2 Months)  
**Date:** 13 Januari 2026

---

## 📋 PHASE 1: CRITICAL FIXES (Week 1-2)

### Goal
Fix critical issues that prevent app from working with real backend.

### Tasks

#### 1.1 Setup Real API Endpoints
**Status:** Not Started  
**Time:** 4 hours  
**Owner:** Backend + Mobile Team  

**Subtasks:**
- [ ] Create `/api/v1/` folder structure in backend
- [ ] Migrate endpoints from `/api/` to `/api/v1/`
- [ ] Document all endpoints (Postman collection)
- [ ] Update `AppConstants` in mobile app
- [ ] Setup environment-based configuration

**Deliverables:**
```
/api/v1/
├── /auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── /peserta/
│   ├── get.php
│   └── update.php
├── /ujian/
│   ├── list.php
│   ├── detail.php
│   └── questions.php
└── /jawaban/
    ├── save.php
    └── get.php
```

#### 1.2 Implement Environment Configuration
**Status:** Not Started  
**Time:** 3 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Create `lib/config/` folder
- [ ] Create `env.dart` with environment selector
- [ ] Add build flavors support
- [ ] Setup different API URLs per environment

**Code Example:**
```dart
// lib/config/env.dart
enum Environment { development, staging, production }

class AppConfig {
  static const env = Environment.development;
  
  static String get apiBaseUrl {
    switch (env) {
      case Environment.development:
        return 'http://localhost:8000/api/v1';
      case Environment.staging:
        return 'https://staging.cbt-lpk.com/api/v1';
      case Environment.production:
        return 'https://api.cbt-lpk.com/api/v1';
    }
  }
}
```

#### 1.3 Remove Development Mode Bypass
**Status:** Not Started  
**Time:** 2 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Remove `isDevelopmentMode` flag
- [ ] Remove conditional logic in `auth_service.dart`
- [ ] Force real Firebase authentication
- [ ] Add mock data for testing (use Firebase Emulator)

**Code Change:**
```dart
// BEFORE - BAD
Future<User?> login(String email, String password) async {
  try {
    final result = await _auth.signInWithEmailAndPassword(...);
    return result.user;
  } catch (e) {
    if (isDevelopmentMode && email.isNotEmpty && password.isNotEmpty) {
      return _auth.currentUser; // ❌ SECURITY RISK!
    }
  }
}

// AFTER - GOOD
Future<User?> login(String email, String password) async {
  final result = await _auth.signInWithEmailAndPassword(
    email: email,
    password: password,
  );
  return result.user;
}
```

#### 1.4 Implement Persistent Storage
**Status:** Not Started  
**Time:** 4 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Add `shared_preferences: ^2.2.0` to pubspec.yaml
- [ ] Create `lib/services/storage_service.dart`
- [ ] Store user token & data after login
- [ ] Load user data on app startup

**Code Example:**
```dart
// lib/services/storage_service.dart
import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static const _userKey = 'user';
  static const _tokenKey = 'token';
  
  Future<void> saveUser(UserModel user) async {
    final prefs = await SharedPreferences.getInstance();
    final json = jsonEncode(user.toJson());
    await prefs.setString(_userKey, json);
  }
  
  Future<UserModel?> getUser() async {
    final prefs = await SharedPreferences.getInstance();
    final json = prefs.getString(_userKey);
    if (json == null) return null;
    return UserModel.fromJson(jsonDecode(json));
  }
  
  Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_userKey);
    await prefs.remove(_tokenKey);
  }
}
```

#### 1.5 Add Basic Error Handling
**Status:** Not Started  
**Time:** 5 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Create `lib/models/failure.dart`
- [ ] Create response wrapper for API calls
- [ ] Add try-catch to all API calls
- [ ] Show user-friendly error messages

**Code Example:**
```dart
// lib/models/failure.dart
abstract class Failure {
  final String message;
  Failure(this.message);
}

class NetworkFailure extends Failure {
  NetworkFailure(String message) : super(message);
}

class ServerFailure extends Failure {
  ServerFailure(String message) : super(message);
}

class ValidationFailure extends Failure {
  ValidationFailure(String message) : super(message);
}

// lib/services/api_service.dart - Usage
Future<Either<Failure, List<TestModel>>> getTests() async {
  try {
    final response = await http.get(Uri.parse('$_baseUrl/ujian'));
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body) as List;
      final tests = data.map((j) => TestModel.fromJson(j)).toList();
      return Right(tests);
    }
    return Left(ServerFailure('Failed to load tests'));
  } catch (e) {
    return Left(NetworkFailure('Network error: $e'));
  }
}
```

### Testing
- [ ] Test app with real API endpoints
- [ ] Verify token persistence
- [ ] Test login without Firebase bypass
- [ ] Test network error scenarios

### Completion Criteria
- ✅ App connects to real backend API
- ✅ User data persists after app restart
- ✅ Development mode removed
- ✅ Error messages shown to user
- ✅ No app crashes on network errors

---

## 📋 PHASE 2: STATE MANAGEMENT & ARCHITECTURE (Week 3-4)

### Goal
Implement proper state management and improve architecture.

### Tasks

#### 2.1 Add Provider State Management
**Status:** Not Started  
**Time:** 6 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Add `provider: ^6.0.0` to pubspec.yaml
- [ ] Create state notifiers for Auth & Test
- [ ] Refactor screens to use Consumer widget
- [ ] Remove manual setState calls

**Code Example:**
```dart
// lib/provider/auth_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';

class AuthState {
  final bool isLoading;
  final UserModel? user;
  final String? error;
  
  AuthState({
    this.isLoading = false,
    this.user,
    this.error,
  });
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthService _authService;
  final StorageService _storageService;
  
  AuthNotifier(this._authService, this._storageService)
      : super(AuthState());
  
  Future<void> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final user = await _authService.login(email, password);
      await _storageService.saveUser(user);
      state = state.copyWith(isLoading: false, user: user);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
    }
  }
}

final authProvider = StateNotifierProvider<AuthNotifier, AuthState>(
  (ref) => AuthNotifier(
    ref.watch(authServiceProvider),
    ref.watch(storageServiceProvider),
  ),
);
```

**Screen Usage:**
```dart
// Before - Manual State
class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool loading = false;
  
  void login() async {
    setState(() => loading = true);
    // ...
    setState(() => loading = false);
  }
}

// After - Provider
class LoginScreen extends StatelessWidget {
  const LoginScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer(
      builder: (context, ref, child) {
        final authState = ref.watch(authProvider);
        
        return Scaffold(
          body: authState.isLoading
              ? const CircularProgressIndicator()
              : LoginForm(
                  onLogin: (email, password) {
                    ref.read(authProvider.notifier)
                        .login(email, password);
                  },
                ),
        );
      },
    );
  }
}
```

#### 2.2 Create Response Models
**Status:** Not Started  
**Time:** 4 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Add `freezed: ^2.4.0` to pubspec.yaml
- [ ] Add `json_serializable: ^6.7.0` to pubspec.yaml
- [ ] Create proper models with validation
- [ ] Update API service to use models
- [ ] Create generic API response wrapper

**Code Example:**
```dart
// lib/models/api_response.dart
class ApiResponse<T> {
  final bool success;
  final String? message;
  final T? data;
  final int? statusCode;
  
  ApiResponse({
    required this.success,
    this.message,
    this.data,
    this.statusCode,
  });
  
  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Object?) fromJsonT,
  ) {
    return ApiResponse(
      success: json['success'] as bool,
      message: json['message'] as String?,
      data: json['data'] != null ? fromJsonT(json['data']) : null,
      statusCode: json['statusCode'] as int?,
    );
  }
}

// lib/models/test_model.dart
@freezed
class TestModel with _$TestModel {
  const factory TestModel({
    required String id,
    required String title,
    required String description,
    required int duration,
    required int questionCount,
  }) = _TestModel;
  
  factory TestModel.fromJson(Map<String, dynamic> json) =>
      _$TestModelFromJson(json);
}
```

#### 2.3 Add Request Authentication
**Status:** Not Started  
**Time:** 3 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Add `dio: ^5.3.0` to replace `http` package
- [ ] Create Dio client with token injection
- [ ] Implement token refresh mechanism
- [ ] Handle 401 errors (token expired)

**Code Example:**
```dart
// lib/services/dio_client.dart
import 'package:dio/dio.dart';

class DioClient {
  late final Dio _dio;
  final StorageService _storageService;
  
  DioClient(this._storageService) {
    _dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.apiBaseUrl,
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 10),
      ),
    );
    
    // Add token interceptor
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _storageService.getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401) {
            // Token expired, refresh or logout
            await _storageService.clear();
            // Navigate to login
          }
          return handler.next(error);
        },
      ),
    );
  }
  
  Future<T> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
  }) async {
    final response = await _dio.get<T>(
      path,
      queryParameters: queryParameters,
    );
    return response.data as T;
  }
  
  Future<T> post<T>(
    String path, {
    Map<String, dynamic>? data,
  }) async {
    final response = await _dio.post<T>(path, data: data);
    return response.data as T;
  }
}
```

#### 2.4 Implement Route Guards
**Status:** Not Started  
**Time:** 3 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Create route observer for protected routes
- [ ] Check auth before navigating
- [ ] Redirect to login if not authenticated
- [ ] Handle deep linking with auth

**Code Example:**
```dart
// lib/core/routes.dart
class Routes {
  static const login = '/';
  static const dashboard = '/dashboard';
  static const test = '/test';
  
  static final routes = <String, WidgetBuilder>{
    login: (_) => const LoginScreen(),
    dashboard: (_) => const DashboardScreen(),
    test: (_) => const TestScreen(),
  };
}

// lib/main.dart
class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: Consumer(
        builder: (context, ref, child) {
          final authState = ref.watch(authProvider);
          
          return authState.user != null
              ? const DashboardScreen()
              : const LoginScreen();
        },
      ),
    );
  }
}
```

### Testing
- [ ] Test state management with Provider DevTools
- [ ] Verify token injection in requests
- [ ] Test error handling for 401/403
- [ ] Test screen navigation with auth

### Completion Criteria
- ✅ Provider state management implemented
- ✅ All API responses typed properly
- ✅ Token automatically injected in requests
- ✅ Protected routes require authentication
- ✅ Screens use Consumer widgets

---

## 📋 PHASE 3: QUALITY & VALIDATION (Week 5-6)

### Goal
Add input validation, testing, and improve code quality.

### Tasks

#### 3.1 Add Input Validation
**Status:** Not Started  
**Time:** 4 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Create `lib/utils/validators.dart`
- [ ] Add form validation to all forms
- [ ] Show validation errors in UI
- [ ] Create custom validators for domain-specific rules

**Code Example:**
```dart
// lib/utils/validators.dart
abstract class Validators {
  static String? validateEmail(String? value) {
    if (value?.isEmpty ?? true) {
      return 'Email tidak boleh kosong';
    }
    final emailRegex = RegExp(r'^[^@]+@[^@]+\.[^@]+');
    if (!emailRegex.hasMatch(value!)) {
      return 'Format email tidak valid';
    }
    return null;
  }
  
  static String? validatePassword(String? value) {
    if (value?.isEmpty ?? true) {
      return 'Password tidak boleh kosong';
    }
    if (value!.length < 6) {
      return 'Password minimal 6 karakter';
    }
    return null;
  }
  
  static String? validateRequired(String? value, String fieldName) {
    if (value?.isEmpty ?? true) {
      return '$fieldName tidak boleh kosong';
    }
    return null;
  }
}

// lib/screens/auth/login_screen.dart
class LoginForm extends StatelessWidget {
  final GlobalKey<FormState> _formKey = GlobalKey();
  
  @override
  Widget build(BuildContext context) {
    return Form(
      key: _formKey,
      child: Column(
        children: [
          TextFormField(
            validator: Validators.validateEmail,
            decoration: const InputDecoration(labelText: 'Email'),
          ),
          TextFormField(
            validator: Validators.validatePassword,
            decoration: const InputDecoration(labelText: 'Password'),
            obscureText: true,
          ),
          ElevatedButton(
            onPressed: () {
              if (_formKey.currentState!.validate()) {
                // Proceed with login
              }
            },
            child: const Text('Login'),
          ),
        ],
      ),
    );
  }
}
```

#### 3.2 Add Unit Tests
**Status:** Not Started  
**Time:** 6 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Create `test/` directory structure
- [ ] Add `mockito: ^5.4.0` to pubspec.yaml
- [ ] Write tests for services
- [ ] Write tests for providers
- [ ] Target 50% code coverage minimum

**Code Example:**
```dart
// test/services/auth_service_test.dart
import 'package:flutter_test/flutter_test.dart';
import 'package:mockito/mockito.dart';

class MockFirebaseAuth extends Mock implements FirebaseAuth {}

void main() {
  group('AuthService', () {
    late AuthService authService;
    late MockFirebaseAuth mockFirebaseAuth;
    
    setUp(() {
      mockFirebaseAuth = MockFirebaseAuth();
      authService = AuthService(mockFirebaseAuth);
    });
    
    test('login returns user on success', () async {
      // Arrange
      final mockUser = Mock<User>();
      when(mockFirebaseAuth.signInWithEmailAndPassword(
        email: anyNamed('email'),
        password: anyNamed('password'),
      )).thenAnswer((_) async => MockUserCredential(mockUser));
      
      // Act
      final result = await authService.login('test@example.com', 'password123');
      
      // Assert
      expect(result, mockUser);
      verify(mockFirebaseAuth.signInWithEmailAndPassword(
        email: 'test@example.com',
        password: 'password123',
      )).called(1);
    });
  });
}
```

#### 3.3 Setup Logging System
**Status:** Not Started  
**Time:** 2 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Add `talker: ^3.2.0` to pubspec.yaml
- [ ] Replace all `print()` with `logger.info()`, etc.
- [ ] Configure log levels per environment
- [ ] Setup log persistence for debugging

**Code Example:**
```dart
// lib/services/logger_service.dart
import 'package:talker_flutter/talker_flutter.dart';

final talker = TalkerFlutter.init();

class LoggerService {
  static void info(String message) => talker.info(message);
  static void warning(String message) => talker.warning(message);
  static void error(String message, [dynamic error, StackTrace? trace]) {
    talker.error(message, error, trace);
  }
  static void debug(String message) => talker.debug(message);
}

// Usage - Replace all print statements
// Before: print('Login attempt');
// After: LoggerService.info('Login attempt');
```

#### 3.4 Add Widget Tests
**Status:** Not Started  
**Time:** 4 hours  
**Owner:** Mobile Team  

**Subtasks:**
- [ ] Create widget tests for screens
- [ ] Test user interactions
- [ ] Test error states
- [ ] Test navigation

**Code Example:**
```dart
// test/screens/login_screen_test.dart
void main() {
  group('LoginScreen', () {
    testWidgets('shows login form', (WidgetTester tester) async {
      await tester.pumpWidget(const TestApp(home: LoginScreen()));
      
      expect(find.byType(TextField), findsWidgets);
      expect(find.byType(ElevatedButton), findsOneWidget);
    });
    
    testWidgets('shows error on invalid email', (WidgetTester tester) async {
      await tester.pumpWidget(const TestApp(home: LoginScreen()));
      
      await tester.enterText(find.byType(TextField).first, 'invalid');
      await tester.tap(find.byType(ElevatedButton));
      await tester.pumpAndSettle();
      
      expect(find.text('Email tidak valid'), findsOneWidget);
    });
  });
}
```

### Testing
- [ ] Test all validators
- [ ] Run unit tests
- [ ] Run widget tests
- [ ] Check code coverage

### Completion Criteria
- ✅ All forms have input validation
- ✅ Unit tests written (50%+ coverage)
- ✅ Widget tests for main screens
- ✅ Logging system in place
- ✅ All print statements replaced

---

## 📋 PHASE 4: POLISH & OPTIMIZATION (Week 7-8)

### Goal
Improve UX, performance, and add nice-to-have features.

### Tasks

#### 4.1 Create Custom Theme
**Status:** Not Started  
**Time:** 3 hours  
**Owner:** Mobile Team  

**Code Example:**
```dart
// lib/theme/app_theme.dart
class AppTheme {
  static ThemeData lightTheme = ThemeData(
    useMaterial3: true,
    colorScheme: ColorScheme.fromSeed(
      seedColor: Colors.blue,
      brightness: Brightness.light,
    ),
    appBarTheme: const AppBarTheme(
      centerTitle: true,
      elevation: 0,
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      contentPadding: const EdgeInsets.all(16),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide.none,
      ),
    ),
  );
}

// lib/main.dart
MaterialApp(
  theme: AppTheme.lightTheme,
  // ...
)
```

#### 4.2 Implement Crash Reporting
**Status:** Not Started  
**Time:** 2 hours  
**Owner:** Mobile Team  

**Code Example:**
```dart
// pubspec.yaml
dependencies:
  firebase_crashlytics: ^3.4.0
  sentry_flutter: ^7.15.0

// lib/main.dart
void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  
  // Enable Crashlytics
  FlutterError.onError = FirebaseCrashlytics.instance.recordFlutterError;
  PlatformDispatcher.instance.onError = (error, stack) {
    FirebaseCrashlytics.instance.recordError(error, stack);
    return true;
  };
  
  runApp(const MyApp());
}
```

#### 4.3 Add Offline Support (Optional)
**Status:** Not Started  
**Time:** 4 hours  
**Owner:** Mobile Team (if needed)  

**Code Example:**
```dart
// lib/services/connectivity_service.dart
import 'package:connectivity_plus/connectivity_plus.dart';

class ConnectivityService {
  final Connectivity _connectivity = Connectivity();
  
  Stream<bool> get connectionStatus =>
      _connectivity.onConnectivityChanged.map((result) {
        return result != ConnectivityResult.none;
      }).distinct();
  
  Future<bool> isConnected() async {
    final result = await _connectivity.checkConnectivity();
    return result != ConnectivityResult.none;
  }
}

// Usage in screens
Consumer(
  builder: (context, ref, child) {
    final isOnline = ref.watch(connectivityProvider);
    
    return isOnline
        ? LoadTestsWidget()
        : const OfflinePlaceholder();
  },
)
```

#### 4.4 Add Analytics (Optional)
**Status:** Not Started  
**Time:** 2 hours  
**Owner:** Mobile Team (if needed)  

```dart
// pubspec.yaml
dependencies:
  firebase_analytics: ^10.7.0

// lib/services/analytics_service.dart
class AnalyticsService {
  final FirebaseAnalytics _analytics = FirebaseAnalytics.instance;
  
  Future<void> logLogin(String method) {
    return _analytics.logLogin(loginMethod: method);
  }
  
  Future<void> logTestStarted(String testId) {
    return _analytics.logEvent(
      name: 'test_started',
      parameters: {'test_id': testId},
    );
  }
}
```

### Completion Criteria
- ✅ Custom theme applied
- ✅ Crash reporting working
- ✅ Analytics tracking key events (optional)
- ✅ Offline mode support (optional)
- ✅ App is visually polished

---

## 🎯 SUMMARY

### Deliverables by Phase

| Phase | Week | Deliverables | Status |
|-------|------|-------------|--------|
| 1 | 1-2 | Real API integration, error handling, persistent storage | Not Started |
| 2 | 3-4 | State management, typed models, request auth, route guards | Not Started |
| 3 | 5-6 | Input validation, unit tests, logging system | Not Started |
| 4 | 7-8 | Custom theme, crash reporting, analytics | Not Started |

### Key Metrics

| Metric | Target |
|--------|--------|
| Code Coverage | 50%+ |
| App Performance (first load) | < 2 seconds |
| API Response Time | < 1 second |
| Crash-Free Users | > 99% |
| Test Pass Rate | 100% |

### Critical Success Factors

1. ✅ Complete Phase 1 (Critical fixes)
2. ✅ Maintain backward compatibility during refactoring
3. ✅ Test thoroughly before each release
4. ✅ Keep documentation updated
5. ✅ Get stakeholder feedback early

---

## 👥 TEAM ASSIGNMENTS

| Role | Responsibility |
|------|-----------------|
| Backend Developer | API endpoints, database, authentication |
| Mobile Developer | Mobile app implementation, testing |
| QA Engineer | Testing, bug reporting |
| Project Manager | Timeline tracking, coordination |

---

## 📅 TIMELINE

```
Week 1-2   ████████░░░░░░░░░░░░░░░░░░░░░░░░ Phase 1 (Critical)
Week 3-4   ░░░░░░░░████████░░░░░░░░░░░░░░░░ Phase 2 (Architecture)
Week 5-6   ░░░░░░░░░░░░░░░░████████░░░░░░░░ Phase 3 (Quality)
Week 7-8   ░░░░░░░░░░░░░░░░░░░░░░░░████████ Phase 4 (Polish)
```

---

**Document Version:** 1.0  
**Last Updated:** 13 Januari 2026  
**Status:** Ready for Execution
