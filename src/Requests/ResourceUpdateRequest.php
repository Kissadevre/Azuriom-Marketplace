<?php

namespace Azuriom\Plugin\Marketplace\Requests;

use Azuriom\Plugin\Marketplace\Rules\AllowedResourceExtension;
use Azuriom\Plugin\Marketplace\Support\ResourceFilePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $resource = $this->route('resource');

        return $this->user() !== null
            && ($resource->isOwnedBy($this->user()) || $this->user()->can('marketplace.edit'));
    }

    public function rules(): array
    {
        $resource = $this->route('resource');
        $allowedExtensions = app(ResourceFilePolicy::class)->allowedExtensions();

        return [
            'version' => [
                'required',
                'string',
                'max:30',
                Rule::notIn(array_filter([$resource->version])),
                Rule::unique('marketplace_resource_updates', 'version')->where('resource_id', $resource->id),
            ],
            'description' => ['required', 'string', 'max:10000'],
            'file' => [
                Rule::requiredIf($resource->delivery_type === 'file'),
                'nullable',
                'file',
                new AllowedResourceExtension($allowedExtensions),
                'max:'.((int) setting('marketplace.max_file_size', 51200)),
            ],
            'external_url' => [
                Rule::requiredIf($resource->delivery_type === 'external'),
                'nullable',
                'url:http,https',
                'max:2000',
            ],
        ];
    }
}
