<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\FormRelease;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;

class FormReleaseStatsController extends Controller
{
    public function show(FormRelease $release): JsonResponse
    {
        $release->load(['releaseQuestions' => fn ($q) => $q->orderBy('order')->with('options')]);

        $submittedIds = Submission::where('form_release_id', $release->id)
            ->where('status', 'submitted')
            ->pluck('id');

        $totalSubmitted = $submittedIds->count();

        $submissionMap = Submission::with('participant:id,name')
            ->whereIn('id', $submittedIds)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->id => $s->participant?->name ?? '—']);

        $allAnswers = Answer::whereIn('submission_id', $submittedIds)
            ->get()
            ->groupBy('release_question_id');

        $stats = $release->releaseQuestions->map(function ($question) use ($allAnswers, $submissionMap, $totalSubmitted) {
            $answers = $allAnswers->get($question->id, collect());

            $stat = [
                'id'             => $question->id,
                'label'          => $question->label,
                'type'           => $question->type,
                'response_count' => 0,
                'options'        => null,
                'unused_count'   => 0,
            ];

            if ($question->hasOptions()) {
                $counts = [];
                foreach ($question->options as $option) {
                    $counts[$option->value] = [
                        'label'       => $option->label,
                        'count'       => 0,
                        'is_other'    => $option->is_other,
                        'respondents' => [],
                    ];
                }

                $respondentIds = [];

                foreach ($answers as $answer) {
                    $respondentIds[]  = $answer->submission_id;
                    $name             = $submissionMap[$answer->submission_id] ?? '—';
                    $json             = $answer->value_json;

                    if ($json === null) {
                        if (isset($counts[$answer->value])) {
                            $counts[$answer->value]['count']++;
                            $counts[$answer->value]['respondents'][] = $name;
                        }
                    } elseif (isset($json['option'])) {
                        $val = $json['option'];
                        if ($val === 'other') {
                            foreach ($counts as &$c) {
                                if ($c['is_other']) { $c['count']++; $c['respondents'][] = $name; break; }
                            }
                            unset($c);
                        } elseif (isset($counts[$val])) {
                            $counts[$val]['count']++;
                            $counts[$val]['respondents'][] = $name;
                        }
                    } elseif (isset($json['values'])) {
                        foreach ($json['values'] as $val) {
                            if ($val === 'other') {
                                foreach ($counts as &$c) {
                                    if ($c['is_other']) { $c['count']++; $c['respondents'][] = $name; break; }
                                }
                                unset($c);
                            } elseif (isset($counts[$val])) {
                                $counts[$val]['count']++;
                                $counts[$val]['respondents'][] = $name;
                            }
                        }
                    }
                }

                $stat['response_count'] = count(array_unique($respondentIds));

                $denominator = max($totalSubmitted, 1);
                $options = array_values(array_map(fn ($c) => [
                    'label'       => $c['label'],
                    'count'       => $c['count'],
                    'pct'         => (int) round($c['count'] / $denominator * 100),
                    'unused'      => $c['count'] === 0,
                    'respondents' => array_values(array_unique($c['respondents'])),
                ], $counts));

                $stat['options']      = $options;
                $stat['unused_count'] = count(array_filter($options, fn ($o) => $o['unused']));
            } else {
                $stat['response_count'] = $answers->filter(fn ($a) =>
                    ($a->value !== null && $a->value !== '')
                    || $a->value_json !== null
                    || $a->file_path !== null
                    || !empty($a->file_paths)
                )->count();
            }

            return $stat;
        })->values()->toArray();

        return response()->json([
            'total_submitted' => $totalSubmitted,
            'stats'           => $stats,
        ]);
    }
}
