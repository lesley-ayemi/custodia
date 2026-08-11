<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    protected const array SORTABLE = ['name', 'email', 'role', 'created_at'];

    public function __construct(
        protected UserService $users,
        protected AuditService $audit,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $sort = in_array($request->string('sort')->value(), self::SORTABLE, strict: true)
            ? $request->string('sort')->value()
            : 'name';

        $direction = $request->string('direction')->value() === 'desc' ? 'desc' : 'asc';

        $users = $query->orderBy($sort, $direction)->paginate(15);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $user = $this->users->create($request->validated());

        $this->audit->log($request->user(), 'created', $user, newValues: [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
        ]);

        return new UserResource($user);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $auditableChanges = $request->safe()->except('password');
        $oldValues = $user->only(array_keys($auditableChanges));

        $this->users->update($user, $request->validated());

        $this->audit->log($request->user(), 'updated', $user, oldValues: $oldValues, newValues: $auditableChanges);

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user): Response
    {
        $this->authorize('delete', $user);

        $this->audit->log($request->user(), 'deactivated', $user, oldValues: ['name' => $user->name, 'email' => $user->email]);

        $this->users->deactivate($user);

        return response()->noContent();
    }
}
