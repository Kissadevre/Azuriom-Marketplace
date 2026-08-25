@csrf
<div class="mb-3"><label class="form-label">@lang('marketplace::messages.fields.category')</label><select name="category_id" class="form-select" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$resource->category_id??null)==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div class="mb-3">
    <label class="form-label">@lang('marketplace::messages.fields.tags')</label>
    @php($selectedTags = array_map('intval', old('tags', isset($resource) ? $resource->tags->pluck('id')->all() : [])))
    @if($tags->isNotEmpty())
        <div class="row g-2">
            @foreach($tags as $tag)
                <div class="col-sm-6 col-lg-4">
                    <label class="form-check border rounded p-3 ps-5 h-100">
                        <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags, true))>
                        <span class="d-flex align-items-center gap-2"><span class="rounded-circle border flex-shrink-0" style="width: .875rem; height: .875rem; background-color: {{ $tag->color }};"></span><span>{{ $tag->name }}</span></span>
                    </label>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-muted small">@lang('marketplace::messages.tags.empty')</div>
    @endif
    @error('tags')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('tags.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    <small class="form-text text-muted">@lang('marketplace::messages.tags.optional_help')</small>
</div>
<div class="row"><div class="col-md-8 mb-3"><label class="form-label">@lang('messages.fields.name')</label><input class="form-control" name="name" maxlength="100" value="{{ old('name',$resource->name??'') }}" required></div><div class="col-md-4 mb-3"><label class="form-label">@lang('marketplace::messages.fields.version')</label><input class="form-control" name="version" value="{{ old('version',$resource->version??'') }}"></div></div>
<div class="mb-3"><label class="form-label">@lang('marketplace::messages.fields.summary')</label><textarea class="form-control" name="summary" maxlength="500" required>{{ old('summary',$resource->summary??'') }}</textarea></div>
<div class="mb-3"><label class="form-label" for="descriptionInput">@lang('marketplace::messages.fields.description')</label><textarea id="descriptionInput" class="form-control html-editor" rows="14" name="description">{{ old('description',$resource->description??'') }}</textarea><small class="form-text text-muted">@lang('marketplace::messages.editor.help')</small></div>
<div class="mb-3"><label class="form-label" for="bannerInput">@lang('marketplace::messages.fields.banner')</label><input id="bannerInput" type="file" name="banner" class="form-control @error('banner') is-invalid @enderror" accept="image/jpeg,image/png,image/webp"><small class="form-text text-muted">@lang('marketplace::messages.banner.help')</small>@error('banner')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
@if(isset($resource) && $resource->banner_path)
<div class="card mb-3" style="max-width: 32rem;"><img src="{{ route('marketplace.resources.banner', $resource) }}" class="card-img-top" style="max-height: 240px; object-fit: cover;" alt="{{ $resource->name }}"><div class="card-body py-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="remove_banner" value="1" id="removeBanner"><label class="form-check-label" for="removeBanner">@lang('marketplace::messages.banner.remove')</label></div></div></div>
@endif
<div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="deliveryType">@lang('marketplace::messages.fields.delivery')</label><select id="deliveryType" name="delivery_type" class="form-select"><option value="file" @selected(old('delivery_type',$resource->delivery_type??'file')==='file')>@lang('marketplace::messages.file')</option><option value="external" @selected(old('delivery_type',$resource->delivery_type??'')==='external')>@lang('marketplace::messages.external')</option></select></div><div class="col-md-6 mb-3"><label class="form-label">@lang('marketplace::messages.fields.price')</label><input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price',$resource->price??0) }}" required></div></div>
<div id="fileDeliveryGroup" class="mb-3"><label class="form-label" for="resourceFile">@lang('marketplace::messages.fields.file')</label><input id="resourceFile" type="file" name="file" class="form-control" accept="{{ app(\Azuriom\Plugin\Marketplace\Support\ResourceFilePolicy::class)->acceptAttribute() }}"></div>
<div id="externalDeliveryGroup" class="mb-3"><label class="form-label" for="resourceExternalUrl">@lang('marketplace::messages.fields.url')</label><input id="resourceExternalUrl" type="url" name="external_url" class="form-control" value="{{ old('external_url',$resource->external_url??'') }}"></div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<button class="btn btn-primary">@lang('messages.actions.save')</button>
@push('footer-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const deliveryType = document.getElementById('deliveryType');
        const fileGroup = document.getElementById('fileDeliveryGroup');
        const fileInput = document.getElementById('resourceFile');
        const externalGroup = document.getElementById('externalDeliveryGroup');
        const externalInput = document.getElementById('resourceExternalUrl');
        const fileIsRequired = {{ isset($resource) && $resource->file_path ? 'false' : 'true' }};

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
    });
</script>
@endpush
@include('marketplace::resources._editor')
