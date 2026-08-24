<?php

namespace Azuriom\Plugin\Marketplace\Rules;

use Azuriom\Plugin\Marketplace\Support\ResourceFilePolicy;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class AllowedResourceExtension implements ValidationRule
{
    public function __construct(private readonly array $allowedExtensions)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());

        if ($extension === ''
            || in_array($extension, ResourceFilePolicy::FORBIDDEN, true)
            || ! in_array($extension, $this->allowedExtensions, true)) {
            $fail(trans('marketplace::messages.validation.file_extension', [
                'extensions' => implode(', ', $this->allowedExtensions),
            ]));
        }
    }
}
