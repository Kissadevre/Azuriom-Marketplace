@csrf

<div class="card mb-4">
    <div class="card-header"><strong>@lang('marketplace::admin.tags.information')</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12"><label class="form-label fw-semibold" for="tagCategory">@lang('marketplace::admin.tags.scope')</label><select id="tagCategory" name="category_id" class="form-select @error('category_id') is-invalid @enderror"><option value="">@lang('marketplace::admin.tags.general')</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $tag->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>@error('category_id')<span class="invalid-feedback">{{ $message }}</span>@enderror<small class="form-text text-muted">@lang('marketplace::admin.tags.scope_help')</small></div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="tagName">@lang('messages.fields.name')</label><input id="tagName" name="name" class="form-control @error('name') is-invalid @enderror" maxlength="100" value="{{ old('name', $tag->name ?? '') }}" required autofocus>@error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="tagSlug">Slug</label><div class="input-group"><span class="input-group-text">#</span><input id="tagSlug" name="slug" class="form-control @error('slug') is-invalid @enderror" maxlength="100" value="{{ old('slug', $tag->slug ?? '') }}" pattern="[A-Za-z0-9_-]+" required>@error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror</div><small class="form-text text-muted">@lang('marketplace::admin.tags.slug_help')</small></div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="tagColor">@lang('marketplace::admin.tags.color')</label><input id="tagColor" type="color" name="color" class="form-control form-control-color @error('color') is-invalid @enderror" value="{{ old('color', $tag->color ?? '#6c757d') }}" required>@error('color')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
            <div class="col-md-6"><label class="form-label fw-semibold" for="tagPosition">@lang('marketplace::admin.tags.position')</label><input id="tagPosition" type="number" inputmode="numeric" min="0" step="1" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $tag->position ?? 0) }}" onkeydown="return !['e', 'E', '+', '-', '.', ','].includes(event.key)" required>@error('position')<span class="invalid-feedback">{{ $message }}</span>@enderror<small class="form-text text-muted">@lang('marketplace::admin.tags.position_help')</small></div>
            <div class="col-12"><label class="form-label fw-semibold" for="tagDescription">@lang('messages.fields.description')</label><textarea id="tagDescription" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="1000">{{ old('description', $tag->description ?? '') }}</textarea>@error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
        </div>
    </div>
</div>

<div class="card mb-4"><div class="card-header"><strong>@lang('marketplace::publishing.tag_title')</strong></div><div class="card-body"><div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="restrict_publishing" value="1" id="restrictTagPublishing" @checked(old('restrict_publishing', isset($tag) && $tag->publish_roles !== null))><label class="form-check-label fw-semibold" for="restrictTagPublishing">@lang('marketplace::publishing.restrict')</label><small class="form-text text-muted d-block">@lang('marketplace::publishing.tag_help')</small></div><div id="tagPublishRoles" class="row g-2">@foreach($roles as $role)<div class="col-sm-6 col-lg-4"><label class="form-check border rounded p-3 ps-5 h-100"><input class="form-check-input tag-publish-role" type="checkbox" name="publish_roles[]" value="{{ $role->id }}" @checked(in_array($role->id,array_map('intval',old('publish_roles',isset($tag)?($tag->publish_roles??[]):[])),true))><span class="badge" style="{{ $role->getBadgeStyle() }}">{{ $role->name }}</span></label></div>@endforeach</div>@error('publish_roles')<div class="text-danger small mt-2">{{ $message }}</div>@enderror</div></div>

<div class="card mb-4"><div class="card-body d-flex align-items-center justify-content-between gap-4"><label for="tagEnabled" class="mb-0"><span class="d-block fw-semibold">@lang('marketplace::admin.tags.enabled')</span><small class="text-muted">@lang('marketplace::admin.tags.enabled_help')</small></label><div class="form-check form-switch fs-4 mb-0"><input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="tagEnabled" @checked(old('is_enabled', $tag->is_enabled ?? true))></div></div></div>

<div class="d-flex justify-content-end gap-2"><a href="{{ route('marketplace.admin.tags.index') }}" class="btn btn-outline-secondary">@lang('messages.actions.cancel')</a><button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button></div>

@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const positionInput = document.getElementById('tagPosition');
        const restrictPublishing = document.getElementById('restrictTagPublishing');
        const publishRoles = document.getElementById('tagPublishRoles');
        const publishRoleInputs = publishRoles.querySelectorAll('.tag-publish-role');
        positionInput.addEventListener('input', () => positionInput.value = positionInput.value.replace(/[^0-9]/g, ''));
        const updatePublishRoles = () => { publishRoles.hidden = ! restrictPublishing.checked; publishRoleInputs.forEach((input) => input.disabled = ! restrictPublishing.checked); };
        restrictPublishing.addEventListener('change', updatePublishRoles);
        updatePublishRoles();
    });
</script>
@endpush
