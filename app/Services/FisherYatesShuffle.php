<?php

namespace App\Services;

/**
 * Fisher-Yates (Knuth) Shuffle Algorithm Implementation
 *
 * Produces an unbiased permutation where every possible arrangement
 * is equally likely. Uses a seeded Mersenne Twister PRNG for
 * deterministic replay — the same seed always produces the same
 * shuffled order, enabling consistent question/option ordering
 * across page reloads for the same student session.
 *
 * Algorithm (modern variant, iterating downward):
 *   for i from n-1 down to 1:
 *       j = random integer with 0 <= j <= i
 *       swap array[i] and array[j]
 *
 * Time complexity: O(n)
 * Space complexity: O(1) — in-place shuffle
 *
 * @see https://en.wikipedia.org/wiki/Fisher%E2%80%93Yates_shuffle
 */
class FisherYatesShuffle
{
    /**
     * Core Fisher-Yates shuffle with seeded PRNG.
     *
     * @param  array  $items  The array to shuffle (will be reindexed).
     * @param  int    $seed   The PRNG seed for deterministic output.
     * @return array  A new shuffled array with 0-based integer keys.
     */
    public static function shuffle(array $items, int $seed): array
    {
        // Reindex to ensure contiguous 0-based keys
        $items = array_values($items);

        // Seed the Mersenne Twister PRNG for deterministic results
        mt_srand($seed);

        $n = count($items);

        // Fisher-Yates: iterate from last element down to second element
        for ($i = $n - 1; $i > 0; $i--) {
            // Generate a random index j where 0 <= j <= i
            $j = mt_rand(0, $i);

            // Swap elements at positions i and j
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        // Reset the PRNG to avoid side effects on other random calls
        mt_srand();

        return $items;
    }

    /**
     * Shuffle question IDs for a student session.
     *
     * Given an ordered list of question IDs from the soal_tes table,
     * returns a new ordering unique to this student's seed.
     *
     * @param  array<int>  $questionIds  Ordered array of question IDs.
     * @param  int         $seed         The student's shuffle seed.
     * @return array<int>  Shuffled question IDs.
     */
    public static function shuffleQuestions(array $questionIds, int $seed): array
    {
        return self::shuffle($questionIds, $seed);
    }

    /**
     * Generate a shuffled option mapping for a single question.
     *
     * Returns an associative array mapping visual labels to original keys.
     * Example: ['A' => 'C', 'B' => 'A', 'C' => 'E', 'D' => 'B', 'E' => 'D']
     * means what the student sees as option "A" is actually the original option "C".
     *
     * @param  array<string>  $availableKeys  Original option keys, e.g. ['A','B','C','D','E'].
     * @param  int            $seed           Unique seed for this question (typically studentSeed + questionIndex).
     * @return array<string, string>  Map of visual label => original key.
     */
    public static function shuffleOptions(array $availableKeys, int $seed): array
    {
        $shuffled = self::shuffle($availableKeys, $seed);

        // Create visual labels: A, B, C, ... matching the count of available options
        $labels = array_slice(['A', 'B', 'C', 'D', 'E'], 0, count($shuffled));

        // Map: visual label => original key
        // e.g., ['A' => 'C'] means visual "A" shows the content of original option "C"
        return array_combine($labels, $shuffled);
    }

    /**
     * Map a student's visual answer back to the original answer key.
     *
     * When a student selects visual "B", and the option map says
     * ['A' => 'C', 'B' => 'A', ...], this returns 'A' (the original key).
     *
     * @param  string                $visualAnswer  The key the student selected (e.g., 'B').
     * @param  array<string, string> $optionMap     The shuffled options map for this question.
     * @return string|null  The original key, or null if the visual answer is invalid.
     */
    public static function mapAnswerToOriginal(string $visualAnswer, array $optionMap): ?string
    {
        return $optionMap[$visualAnswer] ?? null;
    }

    /**
     * Map the original correct answer to its visual position.
     *
     * Given the option map ['A' => 'C', 'B' => 'A', ...] and the original
     * correct answer is 'A', this returns 'B' (because original 'A' was
     * placed at visual position 'B').
     *
     * Useful for admin reports showing the correct answer in the student's context.
     *
     * @param  string                $originalKey  The original correct answer key.
     * @param  array<string, string> $optionMap    The shuffled options map.
     * @return string|null  The visual position of the original key, or null if not found.
     */
    public static function mapOriginalToVisual(string $originalKey, array $optionMap): ?string
    {
        $visual = array_search($originalKey, $optionMap, true);

        return $visual !== false ? $visual : null;
    }

    /**
     * Generate a unique seed for a specific question's options within a student session.
     *
     * Each question needs a different option shuffle. We derive a per-question seed
     * by combining the student's base seed with the question's position index.
     * This ensures deterministic but unique shuffling per question.
     *
     * @param  int  $baseSeed        The student's base shuffle seed.
     * @param  int  $questionIndex   The 0-based index of the question in the shuffled order.
     * @return int  A derived seed unique to this question.
     */
    public static function questionOptionSeed(int $baseSeed, int $questionIndex): int
    {
        // Use a prime multiplier to spread values and avoid collisions
        return ($baseSeed * 31) + $questionIndex + 1;
    }
}
