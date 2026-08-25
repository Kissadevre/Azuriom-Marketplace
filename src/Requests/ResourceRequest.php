<?php

namespace Azuriom\Plugin\Marketplace\Requests;

use Azuriom\Plugin\Marketplace\Rules\AllowedResourceExtension;
use Azuriom\Plugin\Marketplace\Support\ResourceFilePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
            'name' => ['required', 'string', 'max:24', 'regex:/^[\pL\pN ]+$/u'],
            'version' => ['required', 'string', 'max:8', 'regex:/^[A-Za-z0-9._-]+$/'],
            'summary' => ['required', 'string', 'max:150', 'regex:/^[\pL\pN ]+$/u'],
            'description' => ['required', 'string', 'max:50000'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
            'remove_banner' => ['sometimes', 'boolean'],
            'delivery_type' => ['required', Rule::in(['file', 'external'])],
            'file' => [Rule::requiredIf(fn () => $this->input('delivery_type') === 'file' && ! $resource?->file_path), 'nullable', 'file', new AllowedResourceExtension($allowedExtensions), 'max:'.((int) setting('marketplace.max_file_size', 51200))],
            'external_url' => [Rule::requiredIf(fn () => $this->input('delivery_type') === 'external'), 'nullable', 'url:http,https', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::squish(strip_tags((string) $this->input('name', ''))),
            'summary' => Str::squish(strip_tags((string) $this->input('summary', ''))),
            'version' => trim(strip_tags((string) $this->input('version', ''))),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.regex' => trans('marketplace::messages.validation.alpha_numeric_spaces'),
            'summary.regex' => trans('marketplace::messages.validation.alpha_numeric_spaces'),
            'version.regex' => trans('marketplace::messages.validation.version_format'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => trans('messages.fields.name'),
            'summary' => trans('marketplace::messages.fields.summary'),
            'version' => trans('marketplace::messages.fields.version'),
        ];
    }
}
