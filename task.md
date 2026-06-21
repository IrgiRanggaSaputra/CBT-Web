# CBT Web — Implementation Tasks

## Component 1: Database Schema (Migrations)
- [x] Create migration: add `shuffle_seed` to `peserta_tes`
- [x] Create migration: add `shuffled_options` + `jawaban_asli` to `jawaban_pesertas`

## Component 2: Fisher-Yates Shuffle Service
- [x] Create `App\Services\FisherYatesShuffle` class

## Component 3: Backend Controller Changes
- [x] Modify `PesertaController::startTest()` — generate seed, pre-compute option maps
- [x] Modify `PesertaController::getQuestions()` — return shuffled order + options
- [x] Modify `PesertaController::submitAnswer()` — map visual → original answer
- [x] Modify `PesertaController::performFinish()` — score using `jawaban_asli`

## Component 4: Model Updates
- [x] Update `PesertaTes` model — add `shuffle_seed` to fillable
- [x] Update `JawabanPeserta` model — add `shuffled_options`, `jawaban_asli` to fillable/casts

## Component 5: Security Middleware
- [x] Create `EnsureTestSession` middleware
- [x] Create `PreventCheating` middleware
- [x] Register middleware in routes

## Component 6: Admin Enhancements
- [x] Add `importParticipants()` method to AdminController
- [x] Enhance `stats()` with pass/fail counts
- [x] Enhance `reports()` with answered_count/total_questions
- [x] Add monitoring endpoint
- [x] Add new API routes

## Component 7: Frontend Updates
- [x] Update `cbt.js` — shuffled option rendering, anti-cheating JS, participant import, tab focus loss monitoring
- [x] Update `welcome.blade.php` — add participants nav item, import modal
- [x] Update `cbt.css` — import modal styles, monitoring indicators

## Component 8: Database Seeder
- [x] Update `CbtTestDataSeeder` with shuffle_seed data (handled dynamically on test start)

## Verification
- [x] Run migrations successfully
- [x] Run seeders successfully
- [x] Verify Fisher-Yates shuffle produces different orders per seed
