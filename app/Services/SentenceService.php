<?php

namespace App\Services;

use App\Models\Prisoner;
use App\Models\Sentence;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SentenceService
{
    public function __construct(
        protected AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordSentence(Prisoner $prisoner, array $data, User $actor): Sentence
    {
        return DB::transaction(function () use ($prisoner, $data, $actor) {
            $data['prisoner_id'] = $prisoner->id;

            $sentence = Sentence::create($data);

            $this->audit->log($actor, 'recorded sentence', $sentence, newValues: [
                'case_number' => $sentence->case_number,
                'offence' => $sentence->offence,
                'sentence_type' => $sentence->sentence_type->value,
            ]);

            return $sentence;
        });
    }
}
