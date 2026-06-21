<?php

namespace Tests\Unit;

use App\Services\FisherYatesShuffle;
use PHPUnit\Framework\TestCase;

class FisherYatesShuffleTest extends TestCase
{
    /**
     * Test that the shuffle is deterministic when using the same seed.
     */
    public function test_shuffle_is_deterministic()
    {
        $items = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
        $seed = 12345;

        $shuffle1 = FisherYatesShuffle::shuffle($items, $seed);
        $shuffle2 = FisherYatesShuffle::shuffle($items, $seed);

        $this->assertEquals($shuffle1, $shuffle2);
    }

    /**
     * Test that different seeds produce different permutations.
     */
    public function test_different_seeds_produce_different_permutations()
    {
        $items = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $seed1 = 11111;
        $seed2 = 99999;

        $shuffle1 = FisherYatesShuffle::shuffle($items, $seed1);
        $shuffle2 = FisherYatesShuffle::shuffle($items, $seed2);

        // While there is a microscopic chance of a collision, for 10 items it is 1 in 3.6 million.
        $this->assertNotEquals($shuffle1, $shuffle2);
    }

    /**
     * Test shuffling options mapping.
     */
    public function test_shuffle_options_mapping()
    {
        $availableKeys = ['A', 'B', 'C', 'D', 'E'];
        $seed = 42;

        $optionMap = FisherYatesShuffle::shuffleOptions($availableKeys, $seed);

        // Map keys must be A, B, C, D, E (in order)
        $this->assertEquals(['A', 'B', 'C', 'D', 'E'], array_keys($optionMap));

        // Map values must be a permutation of availableKeys
        $this->assertEquals(sort($availableKeys), sort($optionMap));
    }

    /**
     * Test option map mapping logic.
     */
    public function test_answer_mapping_logic()
    {
        // Visual 'A' shows original 'C', visual 'B' shows original 'A', etc.
        $optionMap = [
            'A' => 'C',
            'B' => 'A',
            'C' => 'E',
            'D' => 'B',
            'E' => 'D',
        ];

        // If student picks visual 'B', original answer is 'A'
        $this->assertEquals('A', FisherYatesShuffle::mapAnswerToOriginal('B', $optionMap));

        // If student picks visual 'A', original answer is 'C'
        $this->assertEquals('C', FisherYatesShuffle::mapAnswerToOriginal('A', $optionMap));

        // If the correct original answer is 'E', visual position should be 'C'
        $this->assertEquals('C', FisherYatesShuffle::mapOriginalToVisual('E', $optionMap));

        // If the correct original answer is 'D', visual position should be 'E'
        $this->assertEquals('E', FisherYatesShuffle::mapOriginalToVisual('D', $optionMap));

        // Check non-existent returns null
        $this->assertNull(FisherYatesShuffle::mapAnswerToOriginal('Z', $optionMap));
        $this->assertNull(FisherYatesShuffle::mapOriginalToVisual('Z', $optionMap));
    }

    /**
     * Test question option seed derivation.
     */
    public function test_question_option_seed_derivation()
    {
        $baseSeed = 5000;
        $seedQ0 = FisherYatesShuffle::questionOptionSeed($baseSeed, 0);
        $seedQ1 = FisherYatesShuffle::questionOptionSeed($baseSeed, 1);

        $this->assertNotEquals($seedQ0, $seedQ1);
        
        // Ensure same index produces same derived seed
        $this->assertEquals($seedQ0, FisherYatesShuffle::questionOptionSeed($baseSeed, 0));
    }
}
