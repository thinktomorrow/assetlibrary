<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Support\Str;

class AssetHelper
{
    public static function isImage(string $mimeType): bool
    {
        return Str::endsWith($mimeType, [
            'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp',
        ]);
    }

    public static function getExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public static function getHumanReadableSize(int $sizeInBytes): string
    {
        [$size, $unit] = explode(' ',  \Spatie\MediaLibrary\Support\File::getHumanReadableSize($sizeInBytes));

        return round($size) . ' ' . $unit;
    }

    public static function getBaseName(string $path): string
    {
        return basename($path, '.'.static::getExtension($path));
    }
}
