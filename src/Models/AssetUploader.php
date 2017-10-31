<?php

namespace Thinktomorrow\AssetLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

class AssetUploader extends Model
{
    /**
     * Uploads the file/files or asset by creating the
     * asset that is needed to upload the files too.
     *
     * @param $files
     * @param bool $keepOriginal
     * @return \Illuminate\Support\Collection|null|Asset
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public static function upload($files, $filename = null, $keepOriginal = false)
    {
        $list = collect([]);

        if ($files instanceof Asset) {
            return $files;
        } elseif (is_array($files)) {
            collect($files)->each(function ($file) use ($list, $keepOriginal, $filename) {
                if ($file instanceof Asset) {
                    $list->push($file);
                } else {
                    $asset = new Asset();
                    $asset->save();
                    $list->push(self::uploadToAsset($file, $asset, $filename, $keepOriginal));
                }
            });

            return $list;
        }

        $asset = new Asset();
        $asset->save();

        if (! ($files instanceof File) && ! ($files instanceof UploadedFile)) {
            return;
        }

        return self::uploadToAsset($files, $asset, $filename, $keepOriginal);
    }

    /**
     * Uploads the file/files or asset by creating the
     * asset that is needed to upload the files too.
     *
     * @param string|array $files
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return \Illuminate\Support\Collection|null|Asset
     * @throws \Spatie\MediaLibrary\Exceptions\FileCannotBeAdded
     */
    public static function uploadFromBase64($files, $filename = null, $keepOriginal = false)
    {
        $list = collect([]);

        if (is_array($files)) {
            collect($files)->each(function ($file) use ($list, $filename, $keepOriginal) {
                $asset = Asset::create();

                $list->push(self::uploadBase64ToAsset($file, $asset, $filename, $keepOriginal));
            });

            return $list;
        }

        $asset = new Asset();
        $asset->save();

        return self::uploadBase64ToAsset($files, $asset, $filename, $keepOriginal);
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
            $customProps['dimensions'] = getimagesize($files)[0].' x '.getimagesize($files)[1];
        }

        $fileAdd    = $asset->addMedia($files)->withCustomProperties($customProps);
        if ($keepOriginal) {
            $fileAdd = $fileAdd->preservingOriginal();
        }

        if($filename)
        {
            $fileAdd->setName(substr($filename, 0, strpos($filename, '.')));
            $fileAdd->setFileName($filename);
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
        $fileAdd    = $asset->addMediaFromBase64($file);
        if ($keepOriginal) {
            $fileAdd = $fileAdd->preservingOriginal();
        }

        if($filename)
        {
            $fileAdd->setName(substr($filename, 0, strpos($filename, '.')));
            $fileAdd->setFileName($filename);
        }

        $fileAdd->toMediaCollection();

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
