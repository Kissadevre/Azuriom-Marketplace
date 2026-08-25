<?php

namespace Azuriom\Plugin\Marketplace\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\Marketplace\Models\Restriction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RestrictionController extends Controller
{
    public function index()
    {
        return view('marketplace::admin.restrictions.index', [
            'restrictions' => Restriction::with(['user', 'creator', 'liftedBy'])
                ->latest()
                ->paginate(25),
            'actions' => Restriction::actions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user' => ['required', 'string', 'max:255'],
            'actions' => ['required', 'array', 'min:1'],
            'actions.*' => ['required', 'string', Rule::in(Restriction::actions())],
            'duration' => ['required', Rule::in(['indefinite', 'until'])],
            'expires_at' => ['nullable', 'required_if:duration,until', 'date', 'after:now'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $identifier = trim($data['user']);
        $user = User::query()
            ->where('name', $identifier)
            ->orWhere('email', $identifier)
            ->when(ctype_digit($identifier), fn ($query) => $query->orWhereKey((int) $identifier))
            ->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'user' => trans('marketplace::admin.restrictions.user_not_found'),
            ]);
        }

        $alreadyRestricted = Restriction::query()
            ->active()
            ->where('user_id', $user->id)
            ->get()
            ->flatMap->actions
            ->intersect($data['actions'])
            ->unique()
            ->values();

        if ($alreadyRestricted->isNotEmpty()) {
            throw ValidationException::withMessages([
                'actions' => trans('marketplace::admin.restrictions.already_active', [
                    'actions' => $alreadyRestricted
                        ->map(fn (string $action) => trans('marketplace::admin.restrictions.actions.'.$action))
                        ->join(', '),
                ]),
            ]);
        }

        Restriction::create([
            'user_id' => $user->id,
            'created_by' => $request->user()->id,
            'actions' => array_values(array_unique($data['actions'])),
            'expires_at' => $data['duration'] === 'until' ? $data['expires_at'] : null,
            'reason' => $data['reason'] ?? null,
        ]);

        return to_route('marketplace.admin.restrictions.index')
            ->with('success', trans('marketplace::admin.restrictions.created'));
    }

    public function lift(Request $request, Restriction $restriction)
    {
        if ($restriction->lifted_at === null && ($restriction->expires_at === null || $restriction->expires_at->isFuture())) {
            $restriction->update([
                'lifted_at' => now(),
                'lifted_by' => $request->user()->id,
            ]);
        }

        return back()->with('success', trans('marketplace::admin.restrictions.lifted'));
    }
}
