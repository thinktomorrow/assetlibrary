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
     */
    public static function upload($files, $keepOriginal = false)
    {
        $list = collect([]);

        if ($files instanceof Asset) {
            return $files;
        } elseif (is_array($files)) {
            collect($files)->each(function ($file) use ($list) {
                if ($file instanceof Asset) {
                    $list->push($file);
                } else {
                    $self = new Asset();
                    $self->save();
                    $list->push(self::uploadToAsset($file, $self));
                }
            });

            return $list;
        }

        $self = new Asset();
        $self->save();

        return self::uploadToAsset($files, $self, $keepOriginal);
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param $files
     * @param bool $keepOriginal
     * @param Asset $asset
     * @return $this|null
     */
    public static function uploadToAsset($files, $asset, $keepOriginal = false)
    {
        if (! ($files instanceof File) && ! ($files instanceof UploadedFile)) {
            return;
        }

        $customProps = [];
        if (self::isImage($files)) {
            $customProps['dimensions'] = getimagesize($files)[0].' x '.getimagesize($files)[1];
        }

        $fileAdd    = $asset->addMedia($files)->withCustomProperties($customProps);
        if ($keepOriginal) {
            $fileAdd = $fileAdd->preservingOriginal();
        }

        $fileAdd->toMediaCollection();

        return $asset->load('media');
    }

    private static function isImage($file)
    {
        return str_before($file->getMimetype(), '/') === 'image';
    }
}
