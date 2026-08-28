<?php

namespace Azuriom\Plugin\Marketplace\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'category_id' => ['nullable', 'integer', Rule::exists('marketplace_categories', 'id')],
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
        $this->merge([
            'category_id' => $this->filled('category_id') ? $this->integer('category_id') : null,
            'is_enabled' => $this->boolean('is_enabled'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $tag = $this->route('tag');
            $categoryId = $this->input('category_id');

            if ($validator->errors()->has('category_id') || $tag === null || $categoryId === null) {
                return;
            }

            if ($tag->resources()
                ->where('marketplace_resources.category_id', '!=', (int) $categoryId)
                ->exists()) {
                $validator->errors()->add(
                    'category_id',
                    trans('marketplace::admin.tags.category_conflict')
                );
            }
        });
    }
}
