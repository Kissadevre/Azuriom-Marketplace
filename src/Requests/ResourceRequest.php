<?php

namespace Azuriom\Plugin\Marketplace\Requests;

use Azuriom\Plugin\Marketplace\Rules\AllowedResourceExtension;
use Azuriom\Plugin\Marketplace\Support\ResourceFilePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        $resource = $this->route('resource');
        $allowedExtensions = app(ResourceFilePolicy::class)->allowedExtensions();

        return [
            'category_id' => ['required', Rule::exists('marketplace_categories', 'id')->where('is_enabled', true)],
            'tags' => ['nullable', 'array', 'max:50'],
            'tags.*' => ['integer', 'distinct', Rule::exists('marketplace_tags', 'id')->where('is_enabled', true)],
            'editor_upload_token' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:100'],
            'version' => ['nullable', 'string', 'max:30'],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:50000'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
            'remove_banner' => ['sometimes', 'boolean'],
            'delivery_type' => ['required', Rule::in(['file', 'external'])],
            'file' => [Rule::requiredIf(fn () => $this->input('delivery_type') === 'file' && ! $resource?->file_path), 'nullable', 'file', new AllowedResourceExtension($allowedExtensions), 'max:'.((int) setting('marketplace.max_file_size', 51200))],
            'external_url' => [Rule::requiredIf(fn () => $this->input('delivery_type') === 'external'), 'nullable', 'url:http,https', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ];
    }
}
