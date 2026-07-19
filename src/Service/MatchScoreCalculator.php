<?php

declare(strict_types=1);

namespace App\Service;

final class MatchScoreCalculator
{
    /**
     * Score de compatibilité (0-100) entre les réponses d'un employeur et d'un participant,
     * basé sur la distance euclidienne critère à critère (distance max 3 par critère).
     *
     * @param array<mixed> $employerAnswers
     * @param array<mixed> $participantAnswers
     */
    public function calculate(array $employerAnswers, array $participantAnswers): int
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

        return (int) (100 - (($totalDistance / $maxDistancePossible) * 100));
    }
}
