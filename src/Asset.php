<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
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

    public function exists(): bool
    {
        return true;
    }

    /**
     * @param string $size
     * @return string
     */
    public function url($size = ''): string
    {
        $media = $this->getFirstMedia();

        if ($media == null) {
            return '';
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

        $dimensions = '';
        if (self::isImage($this->getMedia()[0])) {
            $imagesize = getimagesize(public_path($this->url($size??'')));

            $dimensions = $imagesize[0].' x '.$imagesize[1];
        }

        return $dimensions;
    }

    /**
     * @param string|null $size
     * @return string
     */
    public function getWidth($size = null): string
    {
        if ($this->isMediaEmpty()) {
            return '';
        }

        //TODO Check the other sizes as well
        if ($size === 'cropped') {
            $width = explode(',', $this->getMedia()[0]->manipulations['cropped']['manualCrop']);

            return $width[0];
        }

        $width = '';
        if (self::isImage($this->getMedia()[0])) {
            $imagesize = getimagesize(public_path($this->url($size??'')));

            $width = $imagesize[0];
        }

        return $width;
    }

    /**
     * @param string|null $size
     * @return string
     */
    public function getHeight($size = null): string
    {
        if ($this->isMediaEmpty()) {
            return '';
        }

        //TODO Check the other sizes as well
        if ($size === 'cropped') {
            $height = explode(',', $this->getMedia()[0]->manipulations['cropped']['manualCrop']);

            return trim($height[1]);
        }

        $height = '';
        if (self::isImage($this->getMedia()[0])) {
            $imagesize = getimagesize(public_path($this->url($size??'')));

            $height = $imagesize[1];
        }

        return $height;
    }


    /**
     * @param UploadedFile $file
     * @return bool
     */
    private static function isImage($file): bool
    {
        return Str::before($file->mime_type, '/') === 'image';
    }

    public function isUsed()
    {
        $pivots = DB::table('asset_pivots')->where('asset_id', $this->id)->where('unused', false)->get();

        return ! $pivots->isEmpty();
    }

    public function isUnused()
    {
        $pivots = DB::table('asset_pivots')->where('asset_id', $this->id)->where('unused', false)->get();

        return $pivots->isEmpty();
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
        if (! config('thinktomorrow.assetlibrary.allowCropping')) {
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
        $conversions = config('thinktomorrow.assetlibrary.conversions');

        foreach ($conversions as $key => $value) {
            $this->addMediaConversion($key)
                ->width($value['width'])
                ->height($value['height'])
                ->keepOriginalImageFormat()
                ->optimize();
        }

        if (config('thinktomorrow.assetlibrary.allowCropping')) {
            $this->addMediaConversion('cropped')
                ->keepOriginalImageFormat()
                ->optimize();
        }
    }
}
