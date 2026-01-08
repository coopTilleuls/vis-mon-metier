<?php

declare(strict_types=1);

namespace App\Service;

class MatchingHelper
{
    /**
     * @param array<mixed> $employerAnswers
     * @param array<mixed> $participantAnswers
     */
    public function calculateMatchScore(array $employerAnswers, array $participantAnswers): int
    {
        $totalDistance = 0;
        $maxDistancePossible = 0;
        foreach ($employerAnswers as $criteriaId => $employerValue) {
            if (!isset($participantAnswers[$criteriaId])) {
                continue;
            }

            $participantValue = $participantAnswers[$criteriaId];

            $distance = abs($employerValue - $participantValue);

            $totalDistance += $distance;

            $maxDistancePossible += 3;
        }

        if (0 === $maxDistancePossible) {
            return 0;
        }

        $score = 100 - (($totalDistance / $maxDistancePossible) * 100);

        return (int) $score;
    }
}
