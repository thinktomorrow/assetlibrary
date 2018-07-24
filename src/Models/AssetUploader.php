<?php

namespace Thinktomorrow\AssetLibrary\Models;

use Traversable;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;

class AssetUploader extends Model
{
    /**
     * Uploads the file/files or asset by creating the
     * asset that is needed to upload the files too.
     *
     * @param $files
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return \Illuminate\Support\Collection|null|Asset
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public static function upload($files, $filename = null, $keepOriginal = false)
    {
        if ($files instanceof Asset) {
            return $files;
        }

        if (is_array($files) || $files instanceof Traversable) {
            return self::uploadMultiple($files, $keepOriginal);
        }

        if (! ($files instanceof File) && ! ($files instanceof UploadedFile)) {
            return;
        }

        $asset = new Asset();
        $asset->save();

        return self::uploadToAsset($files, $asset, $filename, $keepOriginal);
    }

    private static function uploadMultiple($files, $keepOriginal = false)
    {
        $list = collect([]);
        collect($files)->each(function ($file) use ($list, $keepOriginal) {
            if ($file instanceof Asset) {
                $list->push($file);
            } else {
                $asset = new Asset();
                $asset->save();
                $list->push(self::uploadToAsset($file, $asset, null, $keepOriginal));
            }
        });

        return $list;
    }

    /**
     * Uploads the file/files or asset by creating the
     * asset that is needed to upload the files too.
     *
     * @param $file
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return \Illuminate\Support\Collection|null|Asset
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @internal param array|string $files
     */
    public static function uploadFromBase64($file, $filename = null, $keepOriginal = false)
    {
        $asset = new Asset();
        $asset->save();

        return self::uploadBase64ToAsset($file, $asset, $filename, $keepOriginal);
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param $files
     * @param Asset $asset
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return null|Asset
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public static function uploadToAsset($files, $asset, $filename = null, $keepOriginal = false): ?Asset
    {
        $customProps = [];
        if (self::isImage($files)) {
            $imagesize = getimagesize($files);

            $customProps['dimensions'] = $imagesize[0].' x '.$imagesize[1];
        }

        $fileAdd = $asset->addMedia($files)
                        ->sanitizingFileName(function($filename) {
                            $extension = substr($filename, strrpos($filename, '.') + 1);
                            $filename  = substr($filename, 0, strrpos($filename, '.'));
                            $filename  = str_slug($filename) . '.' . $extension;

                            return strtolower($filename);
                        })
                        ->withCustomProperties($customProps);

        if ($keepOriginal) {
            $fileAdd = $fileAdd->preservingOriginal();
        }

        if ($filename) {
            $fileAdd->usingFileName($filename);
        }


        $fileAdd->toMediaCollection();

        return $asset->load('media');
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param $file
     * @param Asset $asset
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return null|Asset
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     * @internal param $files
     */
    public static function uploadBase64ToAsset($file, $asset, $filename = null, $keepOriginal = false): ?Asset
    {
        //TODO find a way to save the dimensions for base64 uploads
//        $customProps = [];
//        $customProps['dimensions'] = getimagesize($file)[0].' x '.getimagesize($file)[1];

//        $fileAdd    = $asset->addMediaFromBase64($file)->withCustomProperties($customProps);
        $fileAdd = $asset->addMediaFromBase64($file);
        if ($keepOriginal) {
            $fileAdd = $fileAdd->preservingOriginal();
        }

        if ($filename) {
            $fileAdd->usingFileName($filename);
        } else {
            $extension = substr($file, 11, strpos($file, ';') - 11);
            $filename  = pathinfo($file, PATHINFO_BASENAME);
            $fileAdd->setName($filename);
            $fileAdd->setFileName($filename.'.'.$extension);
        }

        $fileAdd->sanitizingFileName(function($filename) {
            $extension = substr($filename, strrpos($filename, '.') + 1);
            $filename  = substr($filename, 0, strrpos($filename, '.'));
            $filename  = str_slug($filename) . '.' . $extension;

            return strtolower($filename);
        })->toMediaCollection();

        return $asset->load('media');
    }

    /**
     * @param $file
     * @return bool
     */
    private static function isImage($file): bool
    {
        return str_before($file->getMimetype(), '/') === 'image';
    }
}
