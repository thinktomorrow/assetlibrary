<?php

namespace Thinktomorrow\AssetLibrary\Models;

use Illuminate\Http\File;
use Spatie\MediaLibrary\Media;
use Thinktomorrow\Locale\Locale;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;

class Asset extends Model implements HasMediaConversions
{
    use HasMediaTrait;

    /**
     * Uploads the file/files or asset by creating the
     * asset that is needed to upload the files too.
     *
     * @param $files
     * @param bool $keepOriginal
     * @return \Illuminate\Support\Collection|null|Asset
     */
    public static function upload($files, $keepOriginal = false)
    {
        $list = collect([]);

        if ($files instanceof self) {
            return $files;
        } elseif (is_array($files)) {
            collect($files)->each(function ($file) use ($list) {
                if ($file instanceof Asset) {
                    $list->push($file);
                } else {
                    $self = new self();
                    $self->save();
                    $list->push($self->uploadToAsset($file));
                }
            });

            return $list;
        }
        $self = new self();
        $self->save();

        return $self->uploadToAsset($files, $keepOriginal);
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param $files
     * @param bool $keepOriginal
     * @return $this|null
     */
    public function uploadToAsset($files, $keepOriginal = false)
    {
        if (! ($files instanceof File) && ! ($files instanceof UploadedFile)) {
            return;
        }

        $customProps = [];
        if (self::isImage($files)) {
            $customProps['dimensions'] = getimagesize($files)[0].' x '.getimagesize($files)[1];
        }

        $fileAdd                   = $this->addMedia($files)->withCustomProperties($customProps);
        if ($keepOriginal) {
            $fileAdd = $fileAdd->preservingOriginal();
        }

        $fileAdd->toMediaCollection();

        return $this->load('media');
    }

    private static function isImage($file)
    {
        return str_before($file->getMimetype(), '/') === 'image';
    }

    /**
     * Attaches this asset instance to the given model and
     * sets the type and locale to the given values and
     * returns the model with the asset relationship.
     *
     * @param Model $model
     * @param string $type
     * @param null $locale
     * @return Model
     */
    public function attachToModel(Model $model, $type = '', $locale = null)
    {
        $asset = $model->assets->where('pivot.type', $type)->where('pivot.locale', $locale);

        if (! $asset->isEmpty() && $asset->first()->pivot->type !== '') {
            $model->assets()->detach($asset->first()->id);
        }

        if (! $locale) {
            $locale = Locale::getDefault();
        }

        $model->assets()->attach($this, ['type' => $type, 'locale' => $locale]);

        return $model->load('assets');
    }

    public function hasFile()
    {
        return (bool) $this->getFileUrl('');
    }

    public function getFilename($size = '')
    {
        return basename($this->getFileUrl($size));
    }

    public function getFileUrl($size = '')
    {
        $media = $this->getMedia();

        if ($media->count() < 1) {
            return asset('assets/back/img/other.png');
        }

        return $media->first()->getUrl($size);
    }

    /**
     * Returns the image url or a fallback specific per filetype.
     *
     * @param string $type
     * @return string
     */
    public function getImageUrl($type = '')
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

    public function getExtensionForFilter()
    {
        if ($extension = $this->getExtensionType()) {
            return $extension;
        }

        return '';
    }

    public function getExtensionType()
    {
        $extension = explode('.', $this->getMedia()[0]->file_name);
        $extension = end($extension);

        if (in_array($extension, ['xls', 'xlsx', 'numbers', 'sheets'])) {
            return 'xls';
        }
        if (in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'])) {
            return 'image';
        }
        if ($extension === 'pdf') {
            return 'pdf';
        }

        return false;
    }

    public function getMimeType()
    {
        return $this->isMediaEmpty() ? '' : $this->getMedia()[0]->mime_type;
    }

    public function isMediaEmpty()
    {
        return $this->getMedia()->isEmpty();
    }

    public function getSize()
    {
        return $this->isMediaEmpty() ? '' : $this->getMedia()[0]->human_readable_size;
    }

    public function getDimensions()
    {
        return $this->isMediaEmpty() ? '' : $this->getMedia()[0]->getCustomProperty('dimensions');
    }

    /**
     * Removes one or more assets by their ids.
     * @param $image_ids
     */
    public static function remove($image_ids)
    {
        if (is_array($image_ids)) {
            foreach ($image_ids as $id) {
                self::where('id', $id)->first()->delete();
            }
        } else {
            self::find($image_ids)->first()->delete();
        }
    }

    /**
     * Returns a collection of all the assets in the library.
     * @return \Illuminate\Support\Collection
     */
    public static function getAllAssets()
    {
        return self::all()->sortByDesc('created_at');
    }

    /**
     * Generates the hidden field that links the file to a specific type.
     *
     * @param string $type
     * @param null $locale
     *
     * @return string
     */
    public static function typeField($type = '', $locale = null, $name = 'type')
    {
        $result = '<input type="hidden" value="'.$type.'" name="';

        if (! $locale) {
            return $result.$name.'">';
        }

        return $result.'trans['.$locale.'][files][]">';
    }

    /**
     * Generates the hidden field that links the file to translations.
     *
     * @param string $locale
     *
     * @return string
     */
    public static function localeField($locale = '')
    {
        return self::typeField($locale, null, 'locale');
    }

    /**
     * Register the conversions that should be performed.
     *
     * @param Media|null $media
     * @return array
     */
    public function registerMediaConversions(Media $media = null)
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
    }
}
