<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Thinktomorrow\AssetLibrary\AssetType\HasAssetType;
use Thinktomorrow\AssetLibrary\AssetType\WithAssetType;

abstract class AbstractAsset extends Model implements HasMedia, AssetContract, HasAssetType
{
    use InteractsWithMedia;
    use ProvidesData;
    use WithAssetType;

    protected $casts = [
        'data' => 'array',
    ];
    public $table = 'assets';
    public $guarded = [];

    /**
     * The asset model always has one collection type. Different collection types for
     * the owning model are set on the asset model instead of the media model.
     */
    const MEDIA_COLLECTION = 'default';
    const ASSET_FORMAT_ORIGINAL = 'original';

    /**
     * Proxy for the data values on the associated pivot. This is the context data
     * relevant and unique for each owner - asset relation.
     */
    public function hasPivotData(string $key): bool
    {
        if (! $this->pivot) {
            return false;
        }

        return $this->pivot->hasData($key);
    }

    /**
     * Proxy for the data values on the associated pivot. This is the context data
     * relevant and unique for each owner - asset relation.
     */
    public function getPivotData(string $key, $default = null)
    {
        if (! $this->pivot) {
            return $default;
        }

        return $this->pivot->getData($key, $default);
    }

    /**
     * Return path of the media file. In case the passed conversion
     * does not exist, the path to the original is returned.
     */
    public function getPath($conversionName = ''): ?string
    {
        return $this->getFirstMediaPath(self::MEDIA_COLLECTION, $conversionName) ?: null;
    }

    /**
     * Return url of the media file. In case the passed conversion
     * does not exist, the url to the original is returned.
     */
    public function getUrl(string $conversionName = '', ?string $format = null): ?string
    {
        if ($conversionName !== '' && $format) {
            $conversionName = $format . '-' . $conversionName;
        }

        if (! $media = $this->getFirstMedia(self::MEDIA_COLLECTION)) {
            return $this->getFallbackMediaUrl(self::MEDIA_COLLECTION) ?: null;
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
    public function getFileName(string $conversionName = ''): ?string
    {
        if (! $path = $this->getFirstMediaPath(self::MEDIA_COLLECTION, $conversionName)) {
            return null;
        }

        return basename($path);
    }

    public function getBaseName(string $conversionName = ''): string
    {
        return basename($this->getFileName($conversionName), '.' . $this->getExtension());
    }

    /**
     * Checks if the conversion exists. It checks if file
     * exists as media record and on the server
     */
    public function exists(string $conversionName = ''): bool
    {
        // In case there is no media model attached to our Asset.
        if (! $path = $this->getFirstMediaPath(self::MEDIA_COLLECTION, $conversionName)) {
            return false;
        }

        // When we specifically check if a conversion exists, we need to explicitly check if the provided path is that of the conversion.
        // This is because Media Library falls back to returning the original path if the converted file does not exist.
        if ($conversionName) {
            $originalPath = $this->getFirstMediaPath(self::MEDIA_COLLECTION, '');
            if ($originalPath == $path) {
                return false;
            }
        }

        return file_exists($path);
    }

    public function getSize(): int
    {
        return $this->getMediaPropertyValue('size', 0);
    }

    public function getHumanReadableSize(): string
    {
        return $this->getMediaPropertyValue('human_readable_size', '');
    }

    public function getMimeType(): ?string
    {
        return $this->getMediaPropertyValue('mime_type');
    }

    public function getExtension(): string
    {
        return $this->getMediaPropertyValue('extension', '');
    }

    public function getExtensionType(): string
    {
        return match (strtolower($this->getExtension())) {
            'xls', 'xlsx', 'numbers', 'sheets' => 'spreadsheet',
            'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp' => 'image',
            'pdf' => 'pdf',
            'mp4', 'webm', 'mpeg', 'mov' => 'video',
            default => 'file'
        };
    }

    public function isImage(): bool
    {
        return $this->getExtensionType() == 'image';
    }

    public function isVideo(): bool
    {
        return $this->getExtensionType() == 'video';
    }

    public function getWidth(string $conversionName = ''): ?int
    {
        return $this->getImageDimensions($conversionName)['width'];
    }

    public function getHeight(string $conversionName = ''): ?int
    {
        return $this->getImageDimensions($conversionName)['height'];
    }

    protected function getImageDimensions(string $conversionName = ''): array
    {
        $result = [
            'width' => null,
            'height' => null,
        ];

        if (! $this->isImage() || ! $this->exists($conversionName)) {
            return $result;
        }

        if ($dimensions = getimagesize($this->getPath($conversionName))) {
            $result['width'] = $dimensions[0];
            $result['height'] = $dimensions[1];
        }

        return $result;
    }

    protected function getMediaPropertyValue(string $property, $default = null)
    {
        if (! $mediaModel = $this->getFirstMedia(self::MEDIA_COLLECTION)) {
            return $default;
        }

        return $mediaModel->{$property};
    }

    /**
     * Register the conversions that should be performed.
     *
     * @param Media|null $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $conversions = config('thinktomorrow.assetlibrary.conversions');
        $formats = config('thinktomorrow.assetlibrary.formats', []);

        // Remove format when original is already in one of these formats
        $originalFormat = null;
        $canKeepOriginalFormat = true;

        if ($media && false !== $formatKey = array_search($media->extension, $formats)) {
            unset($formats[$formatKey]);

            $originalFormat = $media->extension;
            $canKeepOriginalFormat = in_array(strtolower($originalFormat), ['jpg', 'jpeg', 'pjpg', 'png', 'gif']);
        }

        foreach ($conversions as $key => $value) {
            $conversion = $this->addMediaConversion($key)
                ->width($value['width'])
                ->height($value['height']);

            ($canKeepOriginalFormat || ! $originalFormat)
                ? $conversion->keepOriginalImageFormat()
                : $conversion->format($originalFormat);
        }

        foreach ($formats as $format) {
            foreach ($conversions as $conversionName => $values) {
                $this->addMediaConversion($format . '-' . $conversionName)
                    ->width($values['width'])
                    ->height($values['height'])
                    ->format($format);
            }
        }
    }

    public function getMorphClass()
    {
        return 'asset';
    }

    public function getGeneratedConversions(?string $format = null): array
    {
        return $this->getFirstMedia()
            ->getGeneratedConversions()
            ->reject(fn ($conversionCheck) => ! $conversionCheck)
            ->keys()
            ->filter(fn($conversionName) =>
                ! $format
                || ($format !== self::ASSET_FORMAT_ORIGINAL && static::doesConversionNameBelongsToFormat($conversionName))
                || ($format === self::ASSET_FORMAT_ORIGINAL && !static::doesConversionNameBelongsToFormat($conversionName)) )
            ->values()
            ->all();
    }

    public function getUrlsByConversionWidth(?string $format = null): Collection
    {
        $conversionNames = $this->getGeneratedConversions($format);

        return collect($conversionNames)
            ->mapWithKeys(fn ($conversionName) => [$this->getUrl($conversionName) => config('thinktomorrow.assetlibrary.conversions.'.static::removeFormatPrefix($conversionName).'.width')]);
    }

    private static function removeFormatPrefix(string $conversionName): string
    {
        $formats = config('thinktomorrow.assetlibrary.formats', []);

        foreach($formats as $format) {
            if(str_starts_with($conversionName, $format.'-')) {
                return substr($conversionName, strlen($format.'-'));
            }
        }

        return $conversionName;
    }

    private static function doesConversionNameBelongsToFormat(string $conversionName): bool
    {
        $formats = config('thinktomorrow.assetlibrary.formats', []);

        foreach($formats as $format) {
            if(str_starts_with($conversionName, $format.'-')) {
                return true;
            }
        }

        return false;
    }
}
