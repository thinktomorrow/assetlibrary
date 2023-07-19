<?php

namespace Thinktomorrow\AssetLibrary\External;

/**
 * When Asset refers to an external media, hosted on a third party location (e.g. vimeo, cloudinary, youtube, ...)
 * The preview values refer to the local media which acts as a visual representation in the admin environment.
 */
trait InteractsWithLocalPreviewMedia
{
    /**
     * Return path of the media file. In case the passed conversion
     * does not exist, the path to the original is returned.
     */
    public function getPreviewPath($conversionName = ''): ?string
    {
        return $this->getFirstMediaPath(static::MEDIA_COLLECTION, $conversionName) ?: null;
    }

    /**
     * Return url of the media file. In case the passed conversion
     * does not exist, the url to the original is returned.
     */
    public function getPreviewUrl(string $conversionName = '', ?string $format = null): ?string
    {
        if ($conversionName !== '' && $format) {
            $conversionName = $format . '-' . $conversionName;
        }

        if (! $media = $this->getFirstMedia(static::MEDIA_COLLECTION)) {
            return $this->getFallbackMediaUrl(static::MEDIA_COLLECTION) ?: null;
        }

        if ($conversionName !== '') {
            if ($media->hasGeneratedConversion($conversionName)) {
                return $media->getUrl($conversionName) ?: null;
            }

            if (str_contains($conversionName, '-')) {
                $conversionNameWithoutFormat = substr($conversionName, strpos($conversionName, '-') + 1);

                if ($media->hasGeneratedConversion($conversionNameWithoutFormat)) {
                    return $media->getUrl($conversionNameWithoutFormat) ?: null;
                }
            }
        }

        return $media->getUrl() ?: null;
    }

    /**
     * Return filename of the media file. In case the passed conversion
     * does not exist, the name to the original is returned.
     */
    public function getPreviewFileName(string $conversionName = ''): ?string
    {
        if (! $path = $this->getFirstMediaPath(static::MEDIA_COLLECTION, $conversionName)) {
            return null;
        }

        return basename($path);
    }

    public function getPreviewBaseName(string $conversionName = ''): string
    {
        return basename($this->getFileName($conversionName), '.' . $this->getExtension());
    }

    /**
     * Checks if the conversion exists. It checks if file
     * exists as media record and on the server
     */
    public function previewExists(string $conversionName = ''): bool
    {
        // In case there is no media model attached to our Asset.
        if (! $path = $this->getFirstMediaPath(static::MEDIA_COLLECTION, $conversionName)) {
            return false;
        }

        // When we specifically check if a conversion exists, we need to explicitly check if the provided path is that of the conversion.
        // This is because Media Library falls back to returning the original path if the converted file does not exist.
        if ($conversionName) {
            $originalPath = $this->getFirstMediaPath(static::MEDIA_COLLECTION, '');
            if ($originalPath == $path) {
                return false;
            }
        }

        return file_exists($path);
    }

    public function getPreviewSize(): int
    {
        return $this->getMediaPropertyValue('size', 0);
    }

    public function getPreviewHumanReadableSize(): string
    {
        return $this->getMediaPropertyValue('human_readable_size', '');
    }

    public function getPreviewMimeType(): ?string
    {
        return $this->getMediaPropertyValue('mime_type');
    }

    public function getPreviewExtension(): string
    {
        return $this->getMediaPropertyValue('extension', '');
    }

    public function getPreviewExtensionType(): string
    {
        return match (strtolower($this->getPreviewExtension())) {
            'xls', 'xlsx', 'numbers', 'sheets' => 'spreadsheet',
            'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp' => 'image',
            'pdf' => 'pdf',
            'mp4', 'webm', 'mpeg', 'mov' => 'video',
            default => 'file'
        };
    }

    public function isPreviewImage(): bool
    {
        return $this->getPreviewExtensionType() == 'image';
    }

    public function isPreviewVideo(): bool
    {
        return $this->getPreviewExtensionType() == 'video';
    }

    public function getPreviewWidth(string $conversionName = ''): ?int
    {
        return $this->getImageDimensions($conversionName)['width'];
    }

    public function getPreviewHeight(string $conversionName = ''): ?int
    {
        return $this->getImageDimensions($conversionName)['height'];
    }
}
