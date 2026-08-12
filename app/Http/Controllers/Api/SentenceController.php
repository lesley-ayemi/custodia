<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSentenceRequest;
use App\Http\Resources\SentenceResource;
use App\Models\Prisoner;
use App\Models\Sentence;
use App\Services\SentenceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SentenceController extends Controller
{
    public function __construct(
        protected SentenceService $sentences,
    ) {}

    public function indexForPrisoner(Prisoner $prisoner): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Sentence::class);

        return SentenceResource::collection($prisoner->sentences()->get());
    }

    public function store(StoreSentenceRequest $request, Prisoner $prisoner): SentenceResource
    {
        $sentence = $this->sentences->recordSentence($prisoner, $request->validated(), $request->user());

        return new SentenceResource($sentence);
    }

    public function show(Sentence $sentence): SentenceResource
    {
        $this->authorize('view', $sentence);

        return new SentenceResource($sentence->load('prisoner'));
    }
}
