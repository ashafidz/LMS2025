<?php

namespace App\Services;

use App\Models\ProfilingAttempt;
use App\Models\ProfilingComponent;
use App\Models\ProfilingComponentScore;
use App\Models\CourseKnowledgeQuestion;

class ProfilingScoreService
{
    /**
     * Entry point to compute and save all component scores for a completed attempt.
     */
    public function computeAndSave(ProfilingAttempt $attempt): void
    {
        // Ensure attempt has required relations loaded or load them
        $attempt->loadMissing(['likertAnswers.question.dimension', 'mcqAnswers']);

        // Component 1 (Goal Setting), 3 (Motivational Profile), 4 (AI Preference)
        $components = ProfilingComponent::with('dimensions')->get();
        foreach ($components as $component) {
            $this->computeLikertComponent($attempt, $component);
        }

        // Component 2 (Prior Knowledge - MCQ)
        $this->computeMcqComponent($attempt);
    }

    /**
     * Compute score for a Likert-based component.
     * Calculates average score per dimension and contribution percentage (for comp 1 & 3).
     */
    private function computeLikertComponent(ProfilingAttempt $attempt, ProfilingComponent $component): void
    {
        $answers = $attempt->likertAnswers->filter(function ($answer) use ($component) {
            return $answer->question && $answer->question->component_id == $component->id;
        });

        if ($answers->isEmpty()) {
            return;
        }

        $dimensionAvgs = [];
        $totalAvg = 0;

        // Calculate average per dimension
        foreach ($component->dimensions as $dimension) {
            $dimAnswers = $answers->filter(function ($answer) use ($dimension) {
                return $answer->question->dimension_id == $dimension->id;
            });

            $avg = $dimAnswers->avg('answer_value') ?? 0;
            $dimensionAvgs[$dimension->id] = $avg;
            $totalAvg += $avg;
        }

        // Calculate contribution pct and save
        foreach ($component->dimensions as $dimension) {
            $avg = $dimensionAvgs[$dimension->id];
            
            // Contribution only relevant for Goal Setting (1) and Motivational Profile (3)
            // AI Preference (4) mostly uses average.
            $contributionPct = null;
            if ($component->order != 4 && $totalAvg > 0) {
                $contributionPct = ($avg / $totalAvg) * 100;
            }

            // Categorize
            $category = null;
            if ($component->order == 4) {
                $category = $this->categorizeAiPreference($avg);
            } elseif ($contributionPct !== null) {
                $category = $this->categorizeLikert($contributionPct);
            }

            ProfilingComponentScore::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'component_id' => $component->id,
                    'dimension_id' => $dimension->id,
                ],
                [
                    'average_score' => round($avg, 2),
                    'contribution_pct' => $contributionPct !== null ? round($contributionPct, 2) : null,
                    'category' => $category,
                ]
            );
        }
    }

    /**
     * Compute score for Component 2 (Prior Knowledge).
     * Calculates percentage of correct MCQ answers.
     */
    private function computeMcqComponent(ProfilingAttempt $attempt): void
    {
        $totalQuestions = CourseKnowledgeQuestion::where('course_id', $attempt->course_id)
            ->where('is_active', true)
            ->count();

        if ($totalQuestions == 0) {
            return;
        }

        $correctAnswers = $attempt->mcqAnswers->where('is_correct', true)->count();
        $percentage = ($correctAnswers / $totalQuestions) * 100;

        ProfilingComponentScore::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'component_id' => null, // Represents course-specific component
                'dimension_id' => null,
            ],
            [
                'average_score' => round($percentage, 2), // We store % in average_score for MCQ
                'contribution_pct' => null,
                'category' => $this->categorizeKnowledge($percentage),
            ]
        );
    }

    /**
     * Simple categorization for Goal Setting / Motivation based on contribution.
     */
    private function categorizeLikert(float $contribution): string
    {
        // Threshold can be adjusted. E.g., if > 50% in a 2-dimension setup, it's dominant.
        if ($contribution >= 50) return 'Dominan';
        if ($contribution >= 30) return 'Sedang';
        return 'Rendah';
    }

    /**
     * Categorize AI Interaction Preference (1-5 scale).
     */
    private function categorizeAiPreference(float $avg): string
    {
        if ($avg >= 4) return 'Tinggi';
        if ($avg >= 3) return 'Sedang';
        return 'Rendah';
    }

    /**
     * Categorize Prior Knowledge (0-100%).
     */
    private function categorizeKnowledge(float $pct): string
    {
        if ($pct >= 80) return 'Expert';
        if ($pct >= 60) return 'Intermediate';
        if ($pct >= 40) return 'Beginner';
        return 'Novice';
    }
}
