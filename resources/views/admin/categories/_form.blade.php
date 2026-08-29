@csrf

<div class="card mb-4">
    <div class="card-header"><strong>@lang('marketplace::admin.categories.information')</strong></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="categoryName">@lang('messages.fields.name')</label>
                <input id="categoryName" name="name" class="form-control @error('name') is-invalid @enderror" maxlength="100" value="{{ old('name', $category->name ?? '') }}" required autofocus>
                @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="categorySlug">Slug</label>
                <div class="input-group">
                    <span class="input-group-text">/</span>
                    <input id="categorySlug" name="slug" class="form-control @error('slug') is-invalid @enderror" maxlength="100" value="{{ old('slug', $category->slug ?? '') }}" pattern="[A-Za-z0-9_-]+" required>
                    @error('slug')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <small class="form-text text-muted">@lang('marketplace::admin.categories.slug_help')</small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="categoryIcon">@lang('messages.fields.icon')</label>
                <div class="input-group">
                    <span class="input-group-text"><i id="categoryIconPreview" class="{{ old('icon', $category->icon ?? 'bi bi-box') }}" aria-hidden="true"></i></span>
                    <input id="categoryIcon" name="icon" class="form-control @error('icon') is-invalid @enderror" maxlength="100" value="{{ old('icon', $category->icon ?? 'bi bi-box') }}">
                    @error('icon')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <small class="form-text text-muted">@lang('marketplace::admin.categories.icon_help')</small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="categoryPosition">@lang('marketplace::admin.categories.position')</label>
                <input id="categoryPosition" type="number" inputmode="numeric" min="0" step="1" name="position" class="form-control @error('position') is-invalid @enderror" value="{{ old('position', $category->position ?? 0) }}" onkeydown="return !['e', 'E', '+', '-', '.', ','].includes(event.key)" required>
                @error('position')<span class="invalid-feedback">{{ $message }}</span>@enderror
                <small class="form-text text-muted">@lang('marketplace::admin.categories.position_help')</small>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold" for="categoryDescription">@lang('messages.fields.description')</label>
                <textarea id="categoryDescription" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="1000">{{ old('description', $category->description ?? '') }}</textarea>
                @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card mb-4"><div class="card-header"><strong>@lang('marketplace::publishing.category_title')</strong></div><div class="card-body"><div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="restrict_publishing" value="1" id="restrictCategoryPublishing" @checked(old('restrict_publishing', isset($category) && $category->publish_roles !== null))><label class="form-check-label fw-semibold" for="restrictCategoryPublishing">@lang('marketplace::publishing.restrict')</label><small class="form-text text-muted d-block">@lang('marketplace::publishing.category_help')</small></div><div id="categoryPublishRoles" class="row g-2">@foreach($roles as $role)<div class="col-sm-6 col-lg-4"><label class="form-check border rounded p-3 ps-5 h-100"><input class="form-check-input category-publish-role" type="checkbox" name="publish_roles[]" value="{{ $role->id }}" @checked(in_array($role->id,array_map('intval',old('publish_roles',isset($category)?($category->publish_roles??[]):[])),true))><span class="badge" style="{{ $role->getBadgeStyle() }}">{{ $role->name }}</span></label></div>@endforeach</div>@error('publish_roles')<div class="text-danger small mt-2">{{ $message }}</div>@enderror</div></div>

<div class="card mb-4">
    <div class="card-header"><strong>@lang('marketplace::admin.categories.access_settings')</strong></div>
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-4">
            <label for="privateCategory" class="mb-0">
                <span class="d-block fw-semibold">@lang('marketplace::admin.categories.premium')</span>
                <small class="text-muted">@lang('marketplace::admin.categories.roles_help')</small>
            </label>
            <div class="form-check form-switch fs-4 mb-0">
                <input class="form-check-input" type="checkbox" name="is_private" value="1" id="privateCategory" @checked(old('is_private', isset($category) && $category->roles !== null))>
            </div>
        </div>

        <div id="categoryRoles" class="border-top mt-3 pt-3">
            <div class="row g-2">
                @foreach($roles as $role)
                    <div class="col-sm-6 col-lg-4">
                        <label class="form-check border rounded p-3 ps-5 h-100">
                            <input class="form-check-input category-role" type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, array_map('intval', old('roles', isset($category) ? ($category->roles ?? []) : [])), true))>
                            <span class="badge" style="{{ $role->getBadgeStyle() }}">{{ $role->name }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('roles')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body d-flex align-items-center justify-content-between gap-4">
        <label for="categoryEnabled" class="mb-0">
            <span class="d-block fw-semibold">@lang('marketplace::admin.categories.enabled')</span>
            <small class="text-muted">@lang('marketplace::admin.categories.enabled_help')</small>
        </label>
        <div class="form-check form-switch fs-4 mb-0">
            <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="categoryEnabled" @checked(old('is_enabled', $category->is_enabled ?? true))>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('marketplace.admin.categories.index') }}" class="btn btn-outline-secondary">@lang('messages.actions.cancel')</a>
    <button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button>
</div>

@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const privateCategory = document.getElementById('privateCategory');
        const roles = document.getElementById('categoryRoles');
        const roleInputs = roles.querySelectorAll('.category-role');
        const iconInput = document.getElementById('categoryIcon');
        const iconPreview = document.getElementById('categoryIconPreview');
        const positionInput = document.getElementById('categoryPosition');
        const restrictPublishing = document.getElementById('restrictCategoryPublishing');
        const publishRoles = document.getElementById('categoryPublishRoles');
        const publishRoleInputs = publishRoles.querySelectorAll('.category-publish-role');

        const updateRoles = () => {
            roles.hidden = ! privateCategory.checked;
            roleInputs.forEach((input) => input.disabled = ! privateCategory.checked);
        };

        privateCategory.addEventListener('change', updateRoles);
        const updatePublishRoles = () => { publishRoles.hidden = ! restrictPublishing.checked; publishRoleInputs.forEach((input) => input.disabled = ! restrictPublishing.checked); };
        restrictPublishing.addEventListener('change', updatePublishRoles);
        iconInput.addEventListener('input', () => iconPreview.className = iconInput.value || 'bi bi-box');
        positionInput.addEventListener('input', () => {
            positionInput.value = positionInput.value.replace(/[^0-9]/g, '');
        });
        updateRoles();
        updatePublishRoles();
    });
</script>
@endpush
