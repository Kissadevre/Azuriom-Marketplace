<?php

namespace Azuriom\Plugin\Marketplace\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ResourceEditorImageProcessor
{
    private const MAX_PIXELS = 12_000_000;

    /** @return array{contents: string, extension: string, mime: string, width: int, height: int} */
    public function process(UploadedFile $file): array
    {
        $path = $file->getPathname();
        $details = @getimagesize($path);
        $type = @exif_imagetype($path);

        if ($details === false || $type === false) {
            $this->invalid();
        }

        [$width, $height] = $details;
        if ($width < 1 || $height < 1 || $width > 4096 || $height > 4096 || $width * $height > self::MAX_PIXELS) {
            throw ValidationException::withMessages([
                'image' => trans('marketplace::messages.editor_images.dimensions'),
            ]);
        }

        $format = match ($type) {
            IMAGETYPE_JPEG => ['jpeg', 'image/jpeg', 'imagecreatefromjpeg'],
            IMAGETYPE_PNG => ['png', 'image/png', 'imagecreatefrompng'],
            IMAGETYPE_WEBP => ['webp', 'image/webp', 'imagecreatefromwebp'],
            default => null,
        };

        if ($format === null || ! function_exists($format[2])) {
            $this->invalid();
        }

        $image = @$format[2]($path);
        if ($image === false) {
            $this->invalid();
        }

        if ($type === IMAGETYPE_JPEG) {
            $image = $this->orientJpeg($image, $path);
            $width = imagesx($image);
            $height = imagesy($image);
        } else {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        ob_start();
        $encoded = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, null, 88),
            IMAGETYPE_PNG => imagepng($image, null, 7),
            IMAGETYPE_WEBP => imagewebp($image, null, 88),
        };
        $contents = ob_get_clean();
        imagedestroy($image);

        if (! $encoded || ! is_string($contents) || $contents === '') {
            $this->invalid();
        }

        return [
            'contents' => $contents,
            'extension' => $format[0],
            'mime' => $format[1],
            'width' => $width,
            'height' => $height,
        ];
    }

    private function orientJpeg(\GdImage $image, string $path): \GdImage
    {
        $metadata = function_exists('exif_read_data') ? @exif_read_data($path) : false;
        $orientation = is_array($metadata) ? ($metadata['Orientation'] ?? 1) : 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => null,
        };

        if ($angle === null) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);
        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages([
            'image' => trans('marketplace::messages.editor_images.invalid'),
        ]);
    }
}
