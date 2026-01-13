# 📱 MOBILE APP - QUICK REFERENCE GUIDE

**Quick lookup for mobile app structure & key issues**

---

## 🎯 APP OVERVIEW AT A GLANCE

```
App Name:      Aplikasi Ujian (CBT)
Framework:     Flutter 3.19+
Platforms:     iOS, Android, Web, Windows, macOS, Linux
State:         In Development (Prototype)
Backend:       PHP API (currently using mock APIs)
```

---

## 📂 KEY FILES LOCATIONS

### Core Application
| File | Location | Purpose |
|------|----------|---------|
| App Entry | `lib/main.dart` | Firebase init, MaterialApp setup |
| Routes | `lib/core/routes.dart` | Screen navigation mapping |
| Constants | `lib/core/constants.dart` | API endpoints, app config |

### Services (Business Logic)
| File | Location | Purpose |
|------|----------|---------|
| API Calls | `lib/services/api_service.dart` | HTTP requests to backend |
| Auth | `lib/services/auth_service.dart` | Firebase authentication |
| Local Storage | `lib/services/local_service.dart` | In-memory user data (⚠️ NOT PERSISTENT) |

### Data Models
| File | Location | Purpose |
|------|----------|---------|
| User | `lib/models/user_model.dart` | User data structure |
| Test | `lib/models/test_model.dart` | Test/exam structure |
| Question | `lib/models/question_model.dart` | Question structure |

### State Management
| File | Location | Purpose |
|------|----------|---------|
| Auth State | `lib/provider/auth_provider.dart` | Auth state (basic) |
| Test State | `lib/provider/test_provider.dart` | Test state (basic) |

### Screens (UI)
| Screen | Location | Purpose |
|--------|----------|---------|
| Login | `lib/screens/auth/login_screen.dart` | Email/password login |
| Link Account | `lib/screens/auth/link_account_screen.dart` | Link to peserta number |
| Dashboard | `lib/screens/dashboard/dashboard_screen.dart` | Main dashboard |
| Test List | `lib/screens/test/test_list_screen.dart` | Available tests |
| Instructions | `lib/screens/test/instruction_screen.dart` | Test rules/instructions |
| Test Taking | `lib/screens/test/test_screen.dart` | Question display & answering |
| Results | `lib/screens/test/result_screen.dart` | Test scores & results |

---

## 🔄 APP FLOW DIAGRAM

```
┌─────────────────┐
│  App Startup    │
│   main.dart     │
└────────┬────────┘
         │ Firebase Init
         ▼
┌──────────────────────────────┐
│   Check Authentication        │
│   (LocalService.userId)       │
└────────┬────────────┬─────────┘
         │            │
      No │            │ Yes
         ▼            ▼
   ┌──────────┐  ┌──────────────┐
   │  Login   │  │  Dashboard   │
   │  Screen  │  │  Screen      │
   └────┬─────┘  └──────┬───────┘
        │ Success       │
        └──┬────────────┘
           ▼
    ┌──────────────────┐
    │   Link Account   │
    │   Screen         │
    └────┬─────────────┘
         │ Success
         ▼
    ┌──────────────────┐
    │   Dashboard      │
    │   - View Tests   │
    │   - Start Test   │
    │   - View Results │
    └──────────────────┘
```

---

## 🚨 CRITICAL ISSUES QUICK REFERENCE

### Issue #1: Mock API
```
Location: lib/core/constants.dart (Lines 3-7)
Problem:  App uses mockapi.io instead of real backend
Impact:   Cannot connect to production backend
Status:   🔴 CRITICAL - FIX IMMEDIATELY
```

### Issue #2: Development Bypass
```
Location: lib/services/auth_service.dart (Lines 5-6, 15-18)
Problem:  isDevelopmentMode flag allows anyone to login
Impact:   Security breach if set to true
Status:   🔴 CRITICAL - REMOVE IMMEDIATELY
Code:     if (isDevelopmentMode && email.isNotEmpty && password.isNotEmpty) 
          { return _auth.currentUser; } // ANYONE CAN LOGIN!
```

### Issue #3: No Persistent Storage
```
Location: lib/services/local_service.dart (Line 2)
Problem:  static String? userId; // Lost on app restart
Impact:   User must login every time app opens
Status:   🔴 CRITICAL - FIX ASAP
```

### Issue #4: No Error Handling
```
Location: All service files
Problem:  No try-catch for network failures
Impact:   App crashes on poor connection
Status:   🔴 CRITICAL - FIX ASAP
```

### Issue #5: Manual State Management
```
Location: lib/screens/auth/login_screen.dart (Lines 14-18)
Problem:  Manual TextEditingController & setState
Impact:   Not scalable, hard to maintain
Status:   🟠 HIGH - FIX SOON
```

---

## 💾 API ENDPOINTS (Current - Mock)

### Peserta API
```
Current URL: https://696135b2e7aa517cb7985d5c.mockapi.io/api/peserta
Should be:   https://your-domain.com/api/v1/peserta

Endpoints:
- GET /peserta?participantNumber={number}    → Get user by number
```

### Ujian (Test) API
```
Current URL: https://69652809e8ce952ce1f4593a.mockapi.io/api/ujian
Should be:   https://your-domain.com/api/v1/ujian

Endpoints:
- GET /ujian                                  → Get all tests
- GET /ujian/{id}/questions                   → Get test questions
```

### Jawaban (Answer) API
```
Current URL: https://69652aa4e8ce952ce1f46127.mockapi.io/api/jawaban
Should be:   https://your-domain.com/api/v1/jawaban

Endpoints:
- POST /jawaban/save                          → Save answer
- GET /jawaban/{testId}                       → Get test answers
```

---

## 📊 DEPENDENCY TREE

### Current Dependencies
```
pubspec.yaml
├── flutter (SDK)
├── cupertino_icons: ^1.0.8
├── http: ^1.2.1              ⚠️ Basic, no interceptors
├── firebase_core: ^2.27.0    ✅ Good
└── firebase_auth: ^4.19.0    ✅ Good
```

### Missing Critical Dependencies
```
❌ provider: ^6.0.0           # State management
❌ dio: ^5.3.0                # Better HTTP client
❌ shared_preferences: ^2.2.0 # Persistent storage
❌ freezed: ^2.4.0            # Immutable models
❌ talker: ^3.2.0             # Logging
```

---

## 🧪 TEST STRUCTURE

### Current Status: 0% Coverage
```
test/
└── widget_test.dart   (Empty)
```

### What Should Be Added
```
test/
├── unit/
│   ├── services/
│   │   ├── api_service_test.dart
│   │   ├── auth_service_test.dart
│   │   └── storage_service_test.dart
│   └── models/
│       ├── user_model_test.dart
│       └── test_model_test.dart
├── widget/
│   ├── auth/
│   │   └── login_screen_test.dart
│   ├── dashboard/
│   │   └── dashboard_screen_test.dart
│   └── test/
│       ├── test_list_screen_test.dart
│       └── test_screen_test.dart
└── integration/
    └── app_test.dart
```

---

## 🔐 SECURITY CHECKLIST

| Issue | Status | Action |
|-------|--------|--------|
| Mock API in code | ❌ FAIL | Replace with real API |
| Development bypass | ❌ FAIL | Remove flag |
| No HTTPS | ❌ FAIL | Force HTTPS |
| Debug logging | ❌ FAIL | Remove print() calls |
| Token not encrypted | ❌ FAIL | Use Keychain/Keystore |
| No input validation | ❌ FAIL | Add validators |
| Hardcoded URLs | ❌ FAIL | Use config |
| No auth header | ❌ FAIL | Add Bearer token |

---

## 📈 QUICK STATS

| Metric | Current | Target | Status |
|--------|---------|--------|--------|
| Lines of Code | ~500 | ~2000 | 📉 Under-featured |
| Test Coverage | 0% | 50% | ❌ None |
| Type Safety | 60% | 100% | ⚠️ Partial |
| Error Handling | 20% | 95% | ❌ Minimal |
| Security Score | 4/10 | 9/10 | ❌ Poor |
| Architecture | 5/10 | 9/10 | ⚠️ Needs work |

---

## 🎯 IMPLEMENTATION CHECKLIST - PHASE 1

### Week 1-2: Critical Fixes

#### Task 1.1: Replace Mock API
```
File: lib/core/constants.dart
[ ] Create lib/config/env.dart
[ ] Add API_BASE_URL to environment config
[ ] Update pesertaUrl to use real endpoint
[ ] Update ujianUrl to use real endpoint
[ ] Update jawabanUrl to use real endpoint
```

#### Task 1.2: Remove Dev Bypass
```
File: lib/services/auth_service.dart
[ ] Remove isDevelopmentMode constant
[ ] Remove conditional check
[ ] Force real Firebase auth
[ ] Test with real credentials
```

#### Task 1.3: Add Persistent Storage
```
File: lib/services/
[ ] Add shared_preferences to pubspec.yaml
[ ] Create storage_service.dart
[ ] Save user after login
[ ] Load user on app startup
[ ] Clear storage on logout
```

#### Task 1.4: Add Error Handling
```
File: lib/services/api_service.dart
[ ] Create lib/models/failure.dart
[ ] Add try-catch to all methods
[ ] Return Either<Failure, T>
[ ] Show error messages to user
```

#### Task 1.5: Update API Service
```
File: lib/services/api_service.dart
[ ] Use new API base URL
[ ] Add request timeout
[ ] Add response validation
[ ] Add logging
```

---

## 🔗 QUICK LINKS

### Project Files
- [Main App Entry](lib/main.dart)
- [Login Screen](lib/screens/auth/login_screen.dart)
- [API Service](lib/services/api_service.dart)
- [Auth Service](lib/services/auth_service.dart)
- [Routes](lib/core/routes.dart)

### Configuration
- [App Constants](lib/core/constants.dart)
- [Firebase Options](lib/firebase_options.dart)
- [Project Config](pubspec.yaml)

### Testing
- [Tests Directory](test/)
- [Widget Tests](test/widget_test.dart)

---

## 💡 COMMON PATTERNS

### How to Add a New Screen

```dart
// 1. Create screen file
lib/screens/my_feature/my_screen.dart

// 2. Add route
core/routes.dart:
static const myRoute = '/my_route';
myRoute: (_) => const MyScreen(),

// 3. Navigate to it
Navigator.pushNamed(context, Routes.myRoute);

// 4. Add state management
lib/provider/my_provider.dart

// 5. Use in screen
Consumer(
  builder: (context, ref, child) {
    final state = ref.watch(myProvider);
    return MyWidget();
  },
)
```

### How to Make API Call

```dart
// Current (BAD)
Future<List> getTests() async {
  final res = await http.get(Uri.parse("${AppConstants.ujianUrl}/tests"));
  return jsonDecode(res.body);
}

// Better (GOOD)
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

### How to Show Loading State

```dart
// Current (BAD) - Manual setState
bool loading = false;
setState(() => loading = true);

// Better (GOOD) - Provider-based
final testState = ref.watch(testProvider);
if (testState.isLoading) {
  return const CircularProgressIndicator();
}
```

---

## 🆘 TROUBLESHOOTING

### App Crashes on Network Error
**Cause:** No error handling in API service  
**Fix:** Add try-catch and error models

### User Logged Out After App Restart
**Cause:** In-memory storage only  
**Fix:** Use SharedPreferences for persistence

### API Returns Unexpected Type
**Cause:** Using `dynamic` types  
**Fix:** Create typed models with freezed

### State Not Updating in UI
**Cause:** Manual setState or no state provider  
**Fix:** Use Provider with proper notifiers

### "Cannot connect to API" Error
**Cause:** Still using mock API URLs  
**Fix:** Update constants with real API base URL

---

## 📞 QUICK SUPPORT

**Architecture Question?**  
→ Read: `MOBILE_APP_DETAILED_ANALYSIS.md`

**Implementation Help?**  
→ Read: `IMPLEMENTATION_ROADMAP.md`

**Project Structure?**  
→ Read: `STRUCTURE_ANALYSIS.md`

**Code Issues?**  
→ Check: `PROJECT_AUDIT_REPORT.md`

---

**Last Updated:** 13 Januari 2026  
**Version:** 1.0
