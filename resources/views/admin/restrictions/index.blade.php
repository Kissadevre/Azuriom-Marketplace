@extends('admin.layouts.admin')

@section('title', trans('marketplace::admin.restrictions.title'))

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <span class="d-flex align-items-center justify-content-center rounded bg-warning bg-opacity-10 text-warning fs-3" style="width: 3rem; height: 3rem;"><i class="bi bi-person-lock" aria-hidden="true"></i></span>
    <div><h1 class="h3 mb-1">@lang('marketplace::admin.restrictions.title')</h1><p class="text-muted mb-0">@lang('marketplace::admin.restrictions.description')</p></div>
</div>

<div class="card mb-4">
    <div class="card-header"><strong><i class="bi bi-plus-circle me-2" aria-hidden="true"></i>@lang('marketplace::admin.restrictions.create')</strong></div>
    <div class="card-body">
        <form method="POST" action="{{ route('marketplace.admin.restrictions.store') }}">@csrf
            <div class="row g-4">
                <div class="col-lg-5">
                    <label class="form-label" for="restrictionUser">@lang('marketplace::admin.restrictions.user')</label>
                    <input id="restrictionUser" name="user" class="form-control @error('user') is-invalid @enderror" value="{{ old('user') }}" required maxlength="255" placeholder="@lang('marketplace::admin.restrictions.user_placeholder')">
                    <div class="form-text">@lang('marketplace::admin.restrictions.user_help')</div>
                    @error('user')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-lg-7">
                    <label class="form-label d-block">@lang('marketplace::admin.restrictions.restricted_actions')</label>
                    <div class="row g-2">
                        @foreach($actions as $action)
                            <div class="col-md-6"><label class="border rounded p-3 w-100 h-100 d-flex gap-2 align-items-start"><input class="form-check-input mt-1" type="checkbox" name="actions[]" value="{{ $action }}" @checked(in_array($action, old('actions', []), true))><span><strong class="d-block">@lang('marketplace::admin.restrictions.actions.'.$action)</strong><small class="text-muted">@lang('marketplace::admin.restrictions.action_help.'.$action)</small></span></label></div>
                        @endforeach
                    </div>
                    @error('actions')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    @error('actions.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </div>
                <div class="col-lg-5">
                    <label class="form-label" for="restrictionDuration">@lang('marketplace::admin.restrictions.duration')</label>
                    <select id="restrictionDuration" name="duration" class="form-select" data-restriction-duration>
                        <option value="indefinite" @selected(old('duration') === 'indefinite')>@lang('marketplace::admin.restrictions.indefinite')</option>
                        <option value="until" @selected(old('duration') === 'until')>@lang('marketplace::admin.restrictions.until_date')</option>
                    </select>
                </div>
                <div class="col-lg-7" data-restriction-expiration>
                    <label class="form-label" for="restrictionExpiresAt">@lang('marketplace::admin.restrictions.expires_at')</label>
                    <input id="restrictionExpiresAt" type="datetime-local" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}">
                    @error('expires_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="restrictionReason">@lang('marketplace::admin.restrictions.reason')</label>
                    <textarea id="restrictionReason" name="reason" rows="3" maxlength="1000" class="form-control @error('reason') is-invalid @enderror" style="resize: none;">{{ old('reason') }}</textarea>
                    @error('reason')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <button class="btn btn-warning mt-4"><i class="bi bi-person-lock me-1" aria-hidden="true"></i>@lang('marketplace::admin.restrictions.apply')</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center"><strong>@lang('marketplace::admin.restrictions.history')</strong><span class="badge bg-secondary rounded-pill">{{ $restrictions->total() }}</span></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>@lang('marketplace::admin.restrictions.user')</th><th>@lang('marketplace::admin.restrictions.restricted_actions')</th><th>@lang('marketplace::admin.restrictions.duration')</th><th>@lang('marketplace::admin.restrictions.reason')</th><th>@lang('marketplace::admin.restrictions.status')</th><th></th></tr></thead>
            <tbody>
                @forelse($restrictions as $restriction)
                    @php($active = $restriction->lifted_at === null && ($restriction->expires_at === null || $restriction->expires_at->isFuture()))
                    <tr>
                        <td><a href="{{ route('admin.users.edit', $restriction->user) }}" class="fw-semibold">{{ $restriction->user->name }}</a><small class="d-block text-muted">{{ $restriction->user->email }}</small></td>
                        <td>@foreach($restriction->actions as $action)<span class="badge bg-secondary me-1 mb-1">@lang('marketplace::admin.restrictions.actions.'.$action)</span>@endforeach</td>
                        <td class="text-nowrap">{{ $restriction->expires_at ? format_date($restriction->expires_at, true) : trans('marketplace::admin.restrictions.indefinite') }}</td>
                        <td style="max-width: 22rem;">{{ $restriction->reason ?: '—' }}<small class="d-block text-muted">@lang('marketplace::admin.restrictions.applied_by', ['user' => $restriction->creator?->name ?? '—'])</small></td>
                        <td>@if($active)<span class="badge bg-danger">@lang('marketplace::admin.restrictions.active')</span>@elseif($restriction->lifted_at)<span class="badge bg-success">@lang('marketplace::admin.restrictions.lifted_status')</span><small class="d-block text-muted">{{ format_date($restriction->lifted_at, true) }}</small>@else<span class="badge bg-secondary">@lang('marketplace::admin.restrictions.expired')</span>@endif</td>
                        <td class="text-end">@if($active)<form method="POST" action="{{ route('marketplace.admin.restrictions.lift', $restriction) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success"><i class="bi bi-unlock me-1" aria-hidden="true"></i>@lang('marketplace::admin.restrictions.lift')</button></form>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5"><i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i><strong>@lang('marketplace::admin.restrictions.empty')</strong></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@if($restrictions->hasPages())<div class="mt-4">{{ $restrictions->links() }}</div>@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const duration = document.querySelector('[data-restriction-duration]');
        const expiration = document.querySelector('[data-restriction-expiration]');
        const input = expiration?.querySelector('input');
        const updateExpiration = () => {
            const timed = duration?.value === 'until';
            expiration?.classList.toggle('d-none', ! timed);
            if (input) input.required = timed;
        };
        duration?.addEventListener('change', updateExpiration);
        updateExpiration();
    });
</script>
@endpush
