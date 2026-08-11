<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LegalRepresentativeResource;
use App\Models\CourtCase;
use App\Models\LegalRepresentative;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LegalRepresentativeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', CourtCase::class);

        return LegalRepresentativeResource::collection(LegalRepresentative::query()->orderBy('name')->get());
    }

    public function store(Request $request): LegalRepresentativeResource
    {
        $this->authorize('create', CourtCase::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'firm_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        return new LegalRepresentativeResource(LegalRepresentative::create($data));
    }
}
