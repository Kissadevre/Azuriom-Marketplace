@csrf
<input type="hidden" id="editorUploadToken" name="editor_upload_token" value="{{ old('editor_upload_token', $editorUploadToken) }}">
@push('styles')
<style>
    .marketplace-form-card { border-radius: 1rem; }
    .marketplace-form-card .card-header { padding: 1rem 1.25rem; background: transparent; }
    .marketplace-form-sidebar { position: sticky; top: 1.5rem; }
    .marketplace-form-section-icon { width: 2.25rem; height: 2.25rem; }
    .marketplace-tag-option { cursor: pointer; transition: border-color .15s ease, background-color .15s ease; }
    .marketplace-tag-option:hover { border-color: rgba(var(--bs-primary-rgb), .45) !important; background: rgba(var(--bs-primary-rgb), .04); }
    .marketplace-banner-preview { width: 100%; aspect-ratio: 16 / 8.5; object-fit: cover; }
    .marketplace-summary-input { resize: none; }
    .marketplace-editor-card .tox-tinymce { border-color: var(--bs-border-color); border-radius: .65rem; }
    @media (max-width: 991.98px) { .marketplace-form-sidebar { position: static; } }
</style>
@endpush

@if($errors->any())<div class="alert alert-danger rounded-3"><div class="d-flex gap-2"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif

<div class="row g-4 align-items-start">
    <main class="col-lg-8">
        <div class="card marketplace-form-card marketplace-editor-card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="marketplace-form-section-icon d-inline-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
                <strong>@lang('marketplace::messages.tabs.general')</strong>
            </div>
            <div class="card-body p-4">
                <div class="mb-4"><label class="form-label fw-semibold">@lang('messages.fields.name')</label><input class="form-control form-control-lg @error('name') is-invalid @enderror" name="name" maxlength="24" pattern="[\p{L}\p{N} ]+" value="{{ old('name',$resource->name??'') }}" required data-character-counter="nameCounter">@error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror<div class="text-end"><small class="text-muted"><span id="nameCounter">0</span>/24</small></div></div>
                <div class="mb-4"><label class="form-label fw-semibold">@lang('marketplace::messages.fields.summary')</label><textarea class="marketplace-summary-input form-control @error('summary') is-invalid @enderror" name="summary" rows="3" maxlength="150" required data-character-counter="summaryCounter">{{ old('summary',$resource->summary??'') }}</textarea>@error('summary')<span class="invalid-feedback">{{ $message }}</span>@enderror<div class="text-end"><small class="text-muted"><span id="summaryCounter">0</span>/150</small></div></div>
                <div><label class="form-label fw-semibold" for="descriptionInput">@lang('marketplace::messages.fields.description')</label><textarea id="descriptionInput" class="form-control html-editor" rows="14" name="description">{{ old('description',$resource->description??'') }}</textarea><small class="form-text text-muted d-block mt-2"><i class="bi bi-shield-check me-1" aria-hidden="true"></i>@lang('marketplace::messages.editor.help')</small></div>
            </div>
        </div>
    </main>

    <aside class="col-lg-4">
        <div class="marketplace-form-sidebar d-grid gap-4">
            @can('marketplace.pin')<div class="card marketplace-form-card border-primary"><div class="card-body"><div class="form-check form-switch"><input type="hidden" name="is_pinned" value="0"><input class="form-check-input" type="checkbox" role="switch" id="pinnedResource" name="is_pinned" value="1" @checked(old('is_pinned', isset($resource) && $resource->pinned_at !== null))><label class="form-check-label fw-semibold" for="pinnedResource"><i class="bi bi-pin-angle-fill me-1" aria-hidden="true"></i>@lang('marketplace::messages.pin.label')</label><small class="form-text text-muted d-block mt-1">@lang('marketplace::messages.pin.help')</small></div></div></div>@endcan
            <div class="card marketplace-form-card">
                <div class="card-header"><strong><i class="bi bi-sliders me-2" aria-hidden="true"></i>@lang('marketplace::messages.fields.category')</strong></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label" for="resourceCategory">@lang('marketplace::messages.fields.category')</label><select id="resourceCategory" name="category_id" class="form-select" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$resource->category_id??null)==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
                    <div><label class="form-label">@lang('marketplace::messages.fields.version')</label><input class="form-control @error('version') is-invalid @enderror" name="version" maxlength="8" pattern="[A-Za-z0-9._-]+" value="{{ old('version',$resource->version??'') }}" required data-character-counter="versionCounter">@error('version')<span class="invalid-feedback">{{ $message }}</span>@enderror<div class="text-end"><small class="text-muted"><span id="versionCounter">0</span>/8</small></div></div>
                </div>
            </div>

            <div class="card marketplace-form-card">
                <div class="card-header"><strong><i class="bi bi-tags me-2" aria-hidden="true"></i>@lang('marketplace::messages.fields.tags')</strong></div>
                <div class="card-body">
                    @php($selectedTags = array_map('intval', old('tags', isset($resource) ? $resource->tags->pluck('id')->all() : [])))
                    @if($tags->isNotEmpty())
                        <div class="d-grid gap-2">@foreach($tags as $tag)<label class="marketplace-tag-option form-check border rounded-3 p-3 ps-5" data-tag-option data-category-id="{{ $tag->category_id }}"><input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags, true))><span class="d-flex align-items-center justify-content-between gap-2"><span class="d-flex align-items-center gap-2"><span class="rounded-circle border flex-shrink-0" style="width: .875rem; height: .875rem; background-color: {{ $tag->color }};"></span><span>{{ $tag->name }}</span></span><small class="text-muted">{{ $tag->category?->name ?? trans('marketplace::messages.tags.general') }}</small></span></label>@endforeach</div>
                        <div id="marketplaceTagsEmpty" class="text-muted small" hidden>@lang('marketplace::messages.tags.empty_for_category')</div>
                    @else<div class="text-muted small">@lang('marketplace::messages.tags.empty')</div>@endif
                    @error('tags')<div class="text-danger small mt-1">{{ $message }}</div>@enderror @error('tags.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <small class="form-text text-muted d-block mt-2">@lang('marketplace::messages.tags.optional_help')</small>
                </div>
            </div>

            <div class="card marketplace-form-card overflow-hidden">
                <div class="card-header"><strong><i class="bi bi-image me-2" aria-hidden="true"></i>@lang('marketplace::messages.fields.banner')</strong></div>
                @if(isset($resource) && $resource->banner_path)<img src="{{ route('marketplace.resources.banner', $resource) }}" class="marketplace-banner-preview" alt="{{ $resource->name }}">@endif
                <div class="card-body"><input id="bannerInput" type="file" name="banner" class="form-control @error('banner') is-invalid @enderror" accept="image/jpeg,image/png,image/webp"><small class="form-text text-muted d-block mt-2">@lang('marketplace::messages.banner.help')</small>@error('banner')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    @if(isset($resource) && $resource->banner_path)<div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="remove_banner" value="1" id="removeBanner"><label class="form-check-label" for="removeBanner">@lang('marketplace::messages.banner.remove')</label></div>@endif
                </div>
            </div>

            <div class="card marketplace-form-card">
                <div class="card-header"><strong><i class="bi bi-box-arrow-down me-2" aria-hidden="true"></i>@lang('marketplace::messages.fields.delivery')</strong></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label" for="deliveryType">@lang('marketplace::messages.fields.delivery')</label><select id="deliveryType" name="delivery_type" class="form-select"><option value="file" @selected(old('delivery_type',$resource->delivery_type??'file')==='file')>@lang('marketplace::messages.file')</option><option value="external" @selected(old('delivery_type',$resource->delivery_type??'')==='external')>@lang('marketplace::messages.external')</option></select></div>
                    @php($currentPrice = (int) old('price', $resource->price ?? 0))
                    @php($isPaid = old('is_paid', $currentPrice > 0))
                    <div class="form-check form-switch mb-3"><input type="hidden" name="is_paid" value="0"><input class="form-check-input" type="checkbox" role="switch" id="paidResource" name="is_paid" value="1" @checked(filter_var($isPaid, FILTER_VALIDATE_BOOLEAN))><label class="form-check-label fw-semibold" for="paidResource">@lang('marketplace::messages.paid_resource')</label><small class="form-text text-muted d-block">@lang('marketplace::messages.paid_help')</small></div>
                    <div id="coinPriceGroup" class="mb-3"><label class="form-label" for="coinPrice">@lang('marketplace::messages.fields.price')</label><div class="input-group"><span class="input-group-text"><i class="bi bi-coin" aria-hidden="true"></i></span><input id="coinPrice" type="number" step="1" min="1" inputmode="numeric" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ $currentPrice > 0 ? $currentPrice : 0 }}" required></div>@error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    <div id="fileDeliveryGroup"><label class="form-label" for="resourceFile">@lang('marketplace::messages.fields.file')</label><input id="resourceFile" type="file" name="file" class="form-control" accept="{{ app(\Azuriom\Plugin\Marketplace\Support\ResourceFilePolicy::class)->acceptAttribute() }}"></div>
                    <div id="externalDeliveryGroup"><label class="form-label" for="resourceExternalUrl">@lang('marketplace::messages.fields.url')</label><input id="resourceExternalUrl" type="url" name="external_url" class="form-control" value="{{ old('external_url',$resource->external_url??'') }}"></div>
                </div>
            </div>

            @include('elements.captcha', ['center' => true])
            <button class="btn btn-primary btn-lg w-100"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button>
        </div>
    </aside>
</div>
@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const deliveryType = document.getElementById('deliveryType');
        const fileGroup = document.getElementById('fileDeliveryGroup');
        const fileInput = document.getElementById('resourceFile');
        const externalGroup = document.getElementById('externalDeliveryGroup');
        const externalInput = document.getElementById('resourceExternalUrl');
        const paidResource = document.getElementById('paidResource');
        const coinPriceGroup = document.getElementById('coinPriceGroup');
        const coinPrice = document.getElementById('coinPrice');
        const fileIsRequired = {{ isset($resource) && $resource->file_path ? 'false' : 'true' }};
        const categoryInput = document.getElementById('resourceCategory');
        const tagOptions = Array.from(document.querySelectorAll('[data-tag-option]'));
        const tagsEmpty = document.getElementById('marketplaceTagsEmpty');

        const updateTagOptions = () => {
            let visibleTags = 0;

            tagOptions.forEach((option) => {
                const checkbox = option.querySelector('input[type="checkbox"]');
                const appliesToCategory = option.dataset.categoryId === ''
                    || option.dataset.categoryId === categoryInput.value;

                option.hidden = ! appliesToCategory;
                checkbox.disabled = ! appliesToCategory;
                if (! appliesToCategory) checkbox.checked = false;
                if (appliesToCategory) visibleTags++;
            });

            if (tagsEmpty) tagsEmpty.hidden = visibleTags > 0;
        };

        categoryInput.addEventListener('change', updateTagOptions);
        updateTagOptions();

        const updateDeliveryFields = () => {
            const usesFile = deliveryType.value === 'file';

            fileGroup.hidden = ! usesFile;
            fileInput.disabled = ! usesFile;
            fileInput.required = usesFile && fileIsRequired;
            externalGroup.hidden = usesFile;
            externalInput.disabled = usesFile;
            externalInput.required = ! usesFile;
        };

        deliveryType.addEventListener('change', updateDeliveryFields);
        updateDeliveryFields();

        const updatePriceField = () => {
            const isPaid = paidResource.checked;

            coinPriceGroup.hidden = ! isPaid;
            coinPrice.min = isPaid ? '1' : '0';
            coinPrice.value = isPaid ? Math.max(1, Number.parseInt(coinPrice.dataset.paidValue || coinPrice.value, 10) || 1) : 0;
        };

        paidResource.addEventListener('change', () => {
            if (! paidResource.checked && Number.parseInt(coinPrice.value, 10) > 0) {
                coinPrice.dataset.paidValue = coinPrice.value;
            }

            updatePriceField();
        });
        coinPrice.addEventListener('keydown', (event) => {
            if (['e', 'E', '+', '-', '.', ','].includes(event.key)) {
                event.preventDefault();
            }
        });
        coinPrice.addEventListener('input', () => {
            coinPrice.value = coinPrice.value.replace(/\D/g, '');
        });
        updatePriceField();

        document.querySelectorAll('[data-character-counter]').forEach((input) => {
            const counter = document.getElementById(input.dataset.characterCounter);
            const updateCounter = () => counter.textContent = input.value.length;

            input.addEventListener('input', updateCounter);
            updateCounter();
        });
    });
</script>
@endpush
@include('marketplace::resources._editor')
