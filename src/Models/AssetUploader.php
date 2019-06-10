<?php

namespace Thinktomorrow\AssetLibrary\Models;

use Traversable;
use Illuminate\Support\Str;
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
     * @param bool $
     * @return Collection|null|Asset
     * @throws FileCannotBeAdded
     */
    public static function upload($files, $filename = null)
    {
        if ($files instanceof Asset) {
            return $files;
        }

        if (is_array($files) || $files instanceof Traversable) {
            return self::uploadMultiple($files);
        }

        if (! ($files instanceof UploadedFile)) {
            return;
        }

        return self::uploadToAsset($files, Asset::create(), $filename);
    }

    /**
     * Uploads the multiple files or assets by creating the
     * asset that is needed to upload the files too.
     *
     * @param Asset|Traversable|array $files
     * @return Collection
     */
    private static function uploadMultiple($files)
    {
        $list = collect([]);
        collect($files)->each(function ($file) use ($list) {
            if ($file instanceof Asset) {
                $list->push($file);
            } else {
                $asset = new Asset();
                $asset->save();
                $list->push(self::uploadToAsset($file, $asset, null));
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
     * @return Collection|null|Asset
     * @throws FileCannotBeAdded
     */
    public static function uploadFromBase64($file, $filename = null)
    {
        return self::uploadBase64ToAsset($file, Asset::create(), $filename);
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
        return self::uploadFromUrlToAsset($url, Asset::create());
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param UploadedFile $file
     * @param Asset $asset
     * @param string|null $filename
     * @return null|Asset
     * @throws FileCannotBeAdded
     */
    public static function uploadToAsset($file, $asset, $filename = null): ?Asset
    {
        $customProps = [];
        if (self::isImage($file)) {
            $imagesize = getimagesize($file);

            $customProps['dimensions'] = $imagesize[0].' x '.$imagesize[1];
        }

        $fileAdd = $asset->addMedia($file)
                        ->withCustomProperties($customProps);

        $fileAdd = self::prepareOptions($fileAdd, $filename);

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
     * @return null|Asset
     * @throws FileCannotBeAdded
     * @internal param $files
     */
    public static function uploadBase64ToAsset($file, $asset, $filename = null): ?Asset
    {
        $fileAdd = $asset->addMediaFromBase64($file);

        if (! $filename) {
            $extension = substr($file, 11, strpos($file, ';') - 11);
            $filename  = pathinfo($file, PATHINFO_BASENAME).'.'.$extension;
        }

        $fileAdd = self::prepareOptions($fileAdd, $filename);

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

        $fileAdd = self::prepareOptions($fileAdd, $filename);

        $fileAdd->toMediaCollection();

        return $asset->load('media');
    }

    /**
     * @param UploadedFile $file
     * @return bool
     */
    private static function isImage($file): bool
    {
        return Str::before($file->getMimetype() ?? '', '/') === 'image';
    }

    /**
     * Set the possible options on the fileAdder. This includes preserveOriginal
     * and filename.
     *
     * @param FileAdder $fileAdd
     * @param string|null $filename
     * @return FileAdder
     * @throws FileCannotBeAdded
     */
    private static function prepareOptions($fileAdd, $filename): FileAdder
    {
        if ($filename) {
            $fileAdd->usingName(substr($filename, 0, strpos($filename, '.')));
            $fileAdd->usingFileName($filename);
        }

        $fileAdd->preservingOriginal();

        // Sanitize filename by sluggifying the filename without the extension
        $fileAdd->sanitizingFileName(function ($filename) {
            $extension = substr($filename, strrpos($filename, '.') + 1);
            $filename  = substr($filename, 0, strrpos($filename, '.'));
            $filename  = Str::slug($filename).'.'.$extension;

            return strtolower($filename);
        });

        return $fileAdd;
    }
}
