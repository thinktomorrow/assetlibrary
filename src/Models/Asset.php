<?php

namespace Thinktomorrow\AssetLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;
use Spatie\MediaLibrary\Media;
use Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException;
use Thinktomorrow\AssetLibrary\Exceptions\ConfigException;
use Thinktomorrow\AssetLibrary\Exceptions\CorruptMediaException;
use Thinktomorrow\Locale\Locale;

/**
 * @property mixed media
 */
class Asset extends Model implements HasMediaConversions
{
    use HasMediaTrait;

    private $order;

    /**
     * Attaches this asset instance to the given model and
     * sets the type and locale to the given values and
     * returns the model with the asset relationship.
     *
     * @param Model $model
     * @param string $type
     * @param null|string $locale
     * @return Model
     * @throws \Thinktomorrow\AssetLibrary\Exceptions\AssetUploadException
     */
    public function attachToModel(Model $model, $type = '', $locale = null): Model
    {
        if($model->assets()->get()->contains($this))
        {
            throw AssetUploadException::create();
        }

        $model->assets->where('pivot.type', $type)->where('pivot.locale', $locale);

        $locale = $locale ?? Locale::getDefault();

        $model->assets()->attach($this, ['type' => $type, 'locale' => $locale, 'order' => $this->order]);

        return $model->load('assets');
    }

    /**
     * @return bool
     * @throws CorruptMediaException
     */
    public function hasFile(): bool
    {
        return (bool) $this->getFileUrl();
    }

    /**
     * @param string $size
     * @return string
     * @throws CorruptMediaException
     */
    public function getFilename($size = ''): string
    {
        return basename($this->getFileUrl($size));
    }

    /**
     * @param string $size
     * @return string
     * @throws CorruptMediaException
     */
    public function getFileUrl($size = ''): string
    {
        $media = $this->getMedia()->first();

        if (config('assetlibrary.conversionPrefix') && $size != '') {
            $conversionName = $media->name . '_' . $size;
        } else {
            $conversionName = $size;
        }

        if($media == null){
            throw CorruptMediaException::corrupt($this->id);
        }

        return $media->getUrl($conversionName);
    }

    /**
     * Returns the image url or a fallback specific per filetype.
     *
     * @param string $type
     * @return string
     * @throws CorruptMediaException
     */
    public function getImageUrl($type = ''): string
    {
        if ($this->getMedia()->isEmpty()) {
            return asset('assets/back/img/other.png');
        }
        $extension = $this->getExtensionType();
        if ($extension === 'image') {
            return $this->getFileUrl($type);
        } elseif ($extension) {
            return asset('assets/back/img/'.$extension.'.png');
        }

        return asset('assets/back/img/other.png');
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
     * @return string|null
     */
    public function getExtensionType(): ?string
    {
        $extension = explode('.', $this->getMedia()[0]->file_name);
        $extension = end($extension);

        if (in_array(strtolower($extension), ['xls', 'xlsx', 'numbers', 'sheets'])) {
            return 'xls';
        }
        if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) {
            return 'image';
        }
        if (strtolower($extension) === 'pdf') {
            return 'pdf';
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
     * @param null $size
     * @return string
     */
    public function getDimensions($size = null): string
    {
        if($this->isMediaEmpty()) return '';

        //TODO Check the other sizes as well
        if($size === 'cropped')
        {
            $dimensions = explode(',', $this->getMedia()[0]->manipulations['cropped']['manualCrop']);
            return $dimensions[0] . ' x' . $dimensions[1];
        }

        return $this->getMedia()[0]->getCustomProperty('dimensions');
    }

    /**
     * Removes one or more assets by their ids.
     * @param $imageIds
     */
    public static function remove($imageIds)
    {
        if (is_array($imageIds)) {
            foreach ($imageIds as $id) {
                if(!$id) continue;

                $asset = self::where('id', $id)->first();
                $media = $asset->media;

                foreach($media as $file){
                    if(!is_file(public_path($file->getUrl())) || !is_writable(public_path($file->getUrl()))){
                        return;
                    }
                }

                $asset->delete();
            }
        } else {
            if(!$imageIds) return;

            $asset = self::find($imageIds)->first();
            $media = $asset->media;

            foreach($media as $file){
                if(!is_file(public_path($file->getUrl())) || !is_writable(public_path($file->getUrl()))){
                    return;
                }
            }

            self::find($imageIds)->first()->delete();
        }
    }

    /**
     * Returns a collection of all the assets in the library.
     * @return \Illuminate\Support\Collection
     */
    public static function getAllAssets(): Collection
    {
        return self::all()->sortByDesc('created_at');
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
        if(!config('assetlibrary.allowCropping'))
        {
            throw ConfigException::create();
        }
        $this->media[0]->manipulations = [
            'cropped'   => [
                'manualCrop' => $width . ', ' . $height . ', ' . $x . ', ' . $y
            ]
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
    public function registerMediaConversions(Media $media = null): void
    {
        $conversions        = config('assetlibrary.conversions');
        $conversionPrefix   = config('assetlibrary.conversionPrefix');

        foreach ($conversions as $key => $value) {
            if ($conversionPrefix) {
                $conversionName = $media->name.'_'.$key;
            } else {
                $conversionName = $key;
            }

            $this->addMediaConversion($conversionName)
                ->width($value['width'])
                ->height($value['height'])
                ->sharpen(15)
                ->keepOriginalImageFormat()
                ->optimize();
        }

        if(config('assetlibrary.allowCropping'))
        {
            $this->addMediaConversion('cropped')
                ->sharpen(15)
                ->keepOriginalImageFormat()
                ->optimize();
        }
    }

    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }
}
