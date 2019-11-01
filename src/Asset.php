<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use Thinktomorrow\AssetLibrary\Exceptions\ConfigException;
use Thinktomorrow\AssetLibrary\Exceptions\CorruptMediaException;

class Asset extends Model implements HasMedia
{
    use HasMediaTrait;

    private $order;

    /**
     * @return bool
     */
    public function hasFile(): bool
    {
        return (bool) $this->url();
    }

    /**
     * @param string $size
     * @return string
     */
    public function filename($size = ''): string
    {
        return basename($this->url($size));
    }

    /**
     * @param string $size
     * @return string
     */
    public function url($size = ''): string
    {
        $media = $this->getMedia()->first();

        if ($media == null) {
            throw CorruptMediaException::missingMediaRelation($this->id);
        }

        return $media->getUrl($size);
    }

    /**
     * @return bool|string
     */
    public function getExtensionForFilter()
    {
        if ($extension = $this->getExtensionType()) {
            return $extension;
        }

        return '';
    }

    /**
     * @return null|string
     * @throws CorruptMediaException
     */
    public function getExtensionType(): ?string
    {
        $media = $this->getMedia()->first();

        if ($media == null) {
            throw CorruptMediaException::missingMediaRelation($this->id);
        }

        $extension = explode('.', $media->file_name);
        $extension = end($extension);

        if ($extension) {
            if (in_array(strtolower($extension), ['xls', 'xlsx', 'numbers', 'sheets'])) {
                return 'xls';
            }
            if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) {
                return 'image';
            }
            if (strtolower($extension) === 'pdf') {
                return 'pdf';
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->isMediaEmpty() ? '' : $this->getMedia()[0]->mime_type;
    }

    /**
     * @return bool
     */
    public function isMediaEmpty(): bool
    {
        return $this->getMedia()->isEmpty();
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->isMediaEmpty() ? '' : $this->getMedia()[0]->human_readable_size;
    }

    /**
     * @param string|null $size
     * @return string
     */
    public function getDimensions($size = null): string
    {
        if ($this->isMediaEmpty()) {
            return '';
        }

        //TODO Check the other sizes as well
        if ($size === 'cropped') {
            $dimensions = explode(',', $this->getMedia()[0]->manipulations['cropped']['manualCrop']);

            return $dimensions[0].' x'.$dimensions[1];
        }

        return $this->getMedia()[0]->getCustomProperty('dimensions');
    }

    /**
     * @param $width
     * @param $height
     * @param $x
     * @param $y
     * @return $this
     * @throws ConfigException
     */
    public function crop($width, $height, $x, $y)
    {
        if (! config('assetlibrary.allowCropping')) {
            throw ConfigException::croppingDisabled();
        }
        $this->media[0]->manipulations = [
            'cropped'   => [
                'manualCrop' => $width.', '.$height.', '.$x.', '.$y,
            ],
        ];

        $this->media[0]->save();

        return $this;
    }

    /**
     * Register the conversions that should be performed.
     *
     * @param Media|null $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
    {
        $conversions = config('assetlibrary.conversions');

        foreach ($conversions as $key => $value) {
            $this->addMediaConversion($key)
                ->width($value['width'])
                ->height($value['height'])
                ->keepOriginalImageFormat()
                ->optimize();
        }

        if (config('assetlibrary.allowCropping')) {
            $this->addMediaConversion('cropped')
                ->keepOriginalImageFormat()
                ->optimize();
        }
    }
}
