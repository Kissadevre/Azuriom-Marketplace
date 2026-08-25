<?php

namespace Azuriom\Plugin\Marketplace\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('marketplace.admin') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('tag')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('marketplace_tags')->ignore($id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'position' => ['required', 'integer', 'min:0'],
            'is_enabled' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_enabled' => $this->boolean('is_enabled')]);
    }
}
