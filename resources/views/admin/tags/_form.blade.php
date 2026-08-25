@csrf

<div class="card mb-4">
    <div class="card-header"><strong>@lang('marketplace::admin.tags.information')</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold" for="tagName">@lang('messages.fields.name')</label><input id="tagName" name="name" class="form-control @error('name') is-invalid @enderror" maxlength="100" value="{{ old('name', $tag->name ?? '') }}" required autofocus>@error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="tagSlug">Slug</label><div class="input-group"><span class="input-group-text">#</span><input id="tagSlug" name="slug" class="form-control @error('slug') is-invalid @enderror" maxlength="100" value="{{ old('slug', $tag->slug ?? '') }}" pattern="[A-Za-z0-9_-]+" required>@error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror</div><small class="form-text text-muted">@lang('marketplace::admin.tags.slug_help')</small></div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="tagColor">@lang('marketplace::admin.tags.color')</label><input id="tagColor" type="color" name="color" class="form-control form-control-color @error('color') is-invalid @enderror" value="{{ old('color', $tag->color ?? '#6c757d') }}" required>@error('color')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="tagPosition">@lang('marketplace::admin.tags.position')</label><input id="tagPosition" type="number" inputmode="numeric" min="0" step="1" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $tag->position ?? 0) }}" onkeydown="return !['e', 'E', '+', '-', '.', ','].includes(event.key)" required>@error('position')<span class="invalid-feedback">{{ $message }}</span>@enderror<small class="form-text text-muted">@lang('marketplace::admin.tags.position_help')</small></div>
            <div class="col-12"><label class="form-label fw-semibold" for="tagDescription">@lang('messages.fields.description')</label><textarea id="tagDescription" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="1000">{{ old('description', $tag->description ?? '') }}</textarea>@error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
        </div>
    </div>
</div>

<div class="card mb-4"><div class="card-body d-flex align-items-center justify-content-between gap-4"><label for="tagEnabled" class="mb-0"><span class="d-block fw-semibold">@lang('marketplace::admin.tags.enabled')</span><small class="text-muted">@lang('marketplace::admin.tags.enabled_help')</small></label><div class="form-check form-switch fs-4 mb-0"><input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="tagEnabled" @checked(old('is_enabled', $tag->is_enabled ?? true))></div></div></div>

<div class="d-flex justify-content-end gap-2"><a href="{{ route('marketplace.admin.tags.index') }}" class="btn btn-outline-secondary">@lang('messages.actions.cancel')</a><button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button></div>

@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const positionInput = document.getElementById('tagPosition');
        positionInput.addEventListener('input', () => positionInput.value = positionInput.value.replace(/[^0-9]/g, ''));
    });
</script>
@endpush
