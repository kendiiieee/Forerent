<?php

namespace App\Services;

class UrgencyEvaluator
{
    /**
     * Evaluate urgency level based on category and description keywords.
     *
     * Returns 'Level 1' (Critical) through 'Level 4' (Low).
     *
     * Rules:
     *  1. Scan description for emergency/severity keywords
     *  2. Apply category baseline (some categories are inherently more urgent)
     *  3. Take the highest (most urgent) signal found
     */
    public static function evaluate(string $category, string $description): string
    {
        $desc = strtolower($description);

        // Level 1 — Critical / Emergency (safety hazards, total loss of service)
        $level1Keywords = [
            'fire', 'smoke', 'gas leak', 'gas smell', 'flooding', 'flooded',
            'no electricity', 'no power', 'power outage', 'blackout',
            'sewage', 'sewage backup', 'collapse', 'collapsed', 'caving in',
            'exposed wire', 'electrocuted', 'shock', 'electrical shock',
            'no water', 'burst pipe', 'major flood', 'ceiling falling',
            'mold', 'carbon monoxide', 'emergency', 'dangerous', 'unsafe',
            'sparking', 'burning smell',
        ];

        // Level 2 — High (major functional failures)
        $level2Keywords = [
            'leak', 'leaking', 'broken', 'not working', 'malfunction',
            'no hot water', 'clogged', 'overflowing', 'overflow',
            'short circuit', 'tripped breaker', 'pest', 'rats', 'rat',
            'cockroach', 'cockroaches', 'termite', 'termites', 'infestation',
            'cracked pipe', 'water damage', 'heater broken', 'ac broken',
            'air conditioner not working', 'toilet not flushing', 'blocked drain',
            'no drainage', 'flickering lights', 'buzzing outlet',
        ];

        // Level 4 — Low (cosmetic / minor convenience)
        $level4Keywords = [
            'paint', 'peeling', 'scuff', 'scratch', 'squeaky', 'squeak',
            'cosmetic', 'minor', 'small dent', 'discoloration', 'stain',
            'loose handle', 'cabinet door', 'touch up', 'chipped',
        ];

        // Check keywords from most urgent to least (word boundary matching)
        foreach ($level1Keywords as $kw) {
            if (self::matchesWord($desc, $kw)) {
                return 'Level 1';
            }
        }

        foreach ($level2Keywords as $kw) {
            if (self::matchesWord($desc, $kw)) {
                return 'Level 2';
            }
        }

        foreach ($level4Keywords as $kw) {
            if (self::matchesWord($desc, $kw)) {
                return 'Level 4';
            }
        }

        // No keyword match — use category baseline
        $categoryBaseline = match ($category) {
            'Electrical'  => 'Level 2',  // Electrical issues carry inherent risk
            'Structural'  => 'Level 2',  // Structural issues can escalate
            'Plumbing'    => 'Level 3',
            'Appliance'   => 'Level 3',
            'Pest Control'=> 'Level 3',
            default       => 'Level 3',
        };

        return $categoryBaseline;
    }

    /**
     * Check if a keyword appears as a whole word/phrase in the text.
     * Prevents false positives like "rat" matching inside "accurate".
     */
    private static function matchesWord(string $text, string $keyword): bool
    {
        $escaped = preg_quote($keyword, '/');
        return (bool) preg_match('/\b' . $escaped . '\b/', $text);
    }
}
