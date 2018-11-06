<?php

namespace Thinktomorrow\AssetLibrary\Models;

use Traversable;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\FileAdder\FileAdder;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded;

class AssetUploader extends Model
{
    /**
     * Uploads the file/files or asset by creating the
     * asset that is needed to upload the files too.
     *
     * @param Asset|Traversable|array|Collection|UploadedFile $files
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return Collection|null|Asset
     * @throws FileCannotBeAdded
     */
    public static function upload($files, $filename = null, $keepOriginal = false)
    {
        if ($files instanceof Asset) {
            return $files;
        }

        if (is_array($files) || $files instanceof Traversable) {
            return self::uploadMultiple($files, $keepOriginal);
        }

        if (! ($files instanceof UploadedFile)) {
            return;
        }

        $asset = Asset::create();

        return self::uploadToAsset($files, $asset, $filename, $keepOriginal);
    }

    /**
     * Uploads the multiple files or assets by creating the
     * asset that is needed to upload the files too.
     *
     * @param Asset|Traversable|array $files
     * @param bool $keepOriginal
     * @return Collection
     */
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
     * @param string $file
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return Collection|null|Asset
     * @throws FileCannotBeAdded
     */
    public static function uploadFromBase64($file, $filename = null, $keepOriginal = false)
    {
        $asset = Asset::create();

        return self::uploadBase64ToAsset($file, $asset, $filename, $keepOriginal);
    }

    /**
     * Uploads the url by creating the
     * asset that is needed to upload the files too.
     *
     * @param string $url
     * @return Asset
     * @throws FileCannotBeAdded
     */
    public static function uploadFromUrl($url)
    {
        $asset = Asset::create();

        return self::uploadFromUrlToAsset($url, $asset);
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param UploadedFile $file
     * @param Asset $asset
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return null|Asset
     * @throws FileCannotBeAdded
     */
    public static function uploadToAsset($file, $asset, $filename = null, $keepOriginal = false): ?Asset
    {
        $customProps = [];
        if (self::isImage($file)) {
            $imagesize = getimagesize($file);

            $customProps['dimensions'] = $imagesize[0].' x '.$imagesize[1];
        }

        $fileAdd = $asset->addMedia($file)
                        ->withCustomProperties($customProps);

        $fileAdd = self::prepareOptions($fileAdd, $keepOriginal, $filename);

        $fileAdd->withResponsiveImages()->toMediaCollection();

        return $asset->load('media');
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param string $file
     * @param Asset $asset
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return null|Asset
     * @throws FileCannotBeAdded
     * @internal param $files
     */
    public static function uploadBase64ToAsset($file, $asset, $filename = null, $keepOriginal = false): ?Asset
    {
        $fileAdd = $asset->addMediaFromBase64($file);

        if (! $filename) {
            $extension = substr($file, 11, strpos($file, ';') - 11);
            $filename  = pathinfo($file, PATHINFO_BASENAME).'.'.$extension;
        }

        $fileAdd = self::prepareOptions($fileAdd, $keepOriginal, $filename);

        $fileAdd->toMediaCollection();

        return $asset->load('media');
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param string $url
     * @param Asset $asset
     * @return Asset
     * @throws FileCannotBeAdded
     */
    public static function uploadFromUrlToAsset($url, $asset): Asset
    {
        $fileAdd = $asset->addMediaFromUrl($url);

        $filename = substr($url, strrpos($url, '/') + 1);
        $fileAdd->setName($filename);

        $fileAdd = self::prepareOptions($fileAdd, false, $filename);

        $fileAdd->toMediaCollection();

        return $asset->load('media');
    }

    /**
     * @param UploadedFile $file
     * @return bool
     */
    private static function isImage($file): bool
    {
        return str_before($file->getMimetype() ?? '', '/') === 'image';
    }

    /**
     * Set the possible options on the fileAdder. This includes preserveOriginal
     * and filename.
     *
     * @param FileAdder $fileAdd
     * @param string|null $filename
     * @param bool $keepOriginal
     * @return FileAdder
     * @throws FileCannotBeAdded
     */
    private static function prepareOptions($fileAdd, $keepOriginal, $filename): FileAdder
    {
        if ($keepOriginal) {
            $fileAdd = $fileAdd->preservingOriginal();
        }

        if ($filename) {
            $fileAdd->usingName(substr($filename, 0, strpos($filename, '.')));
            $fileAdd->usingFileName($filename);
        }

        // Sanitize filename by sluggifying the filename without the extension
        $fileAdd->sanitizingFileName(function ($filename) {
            $extension = substr($filename, strrpos($filename, '.') + 1);
            $filename  = substr($filename, 0, strrpos($filename, '.'));
            $filename  = str_slug($filename).'.'.$extension;

            return strtolower($filename);
        });

        return $fileAdd;
    }
}
