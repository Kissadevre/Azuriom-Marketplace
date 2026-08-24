<?php

namespace Azuriom\Plugin\Marketplace\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        $resource = $this->route('resource');
        return [
            'category_id' => ['required', Rule::exists('marketplace_categories', 'id')->where('is_enabled', true)],
            'name' => ['required', 'string', 'max:100'],
            'version' => ['nullable', 'string', 'max:30'],
            'summary' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:50000'],
            'delivery_type' => ['required', Rule::in(['file', 'external'])],
            'file' => [Rule::requiredIf(fn () => $this->input('delivery_type') === 'file' && ! $resource?->file_path), 'nullable', 'file', 'max:'.((int) setting('marketplace.max_file_size', 51200))],
            'external_url' => [Rule::requiredIf(fn () => $this->input('delivery_type') === 'external'), 'nullable', 'url:http,https', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
        ];
    }
}
