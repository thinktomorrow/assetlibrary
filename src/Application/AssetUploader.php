<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Thinktomorrow\AssetLibrary\Asset;
use Traversable;

class AssetUploader
{
    /**
     * Uploads the file/files or asset by creating the
     * asset that is needed to upload the files too.
     *
     * @param Asset|Traversable|array|Collection|UploadedFile $files
     * @param string|null $filename
     * @return Collection|null|Asset
     * @throws FileCannotBeAdded
     */
    public static function upload($files, ?string $filename = null, string $collection = 'default', string $disk = '')
    {
        if (! $files) {
            throw new InvalidArgumentException();
        }

        if ($files instanceof Asset) {
            return $files;
        }

        if (is_array($files) || $files instanceof Traversable) {
            return self::uploadMultiple($files);
        }

        if (! ($files instanceof UploadedFile)) {
            throw new InvalidArgumentException();
        }

        return self::uploadToAsset($files, Asset::create(), $filename, false, $collection, $disk);
    }

    /**
     * Uploads the multiple files or assets by creating the
     * asset that is needed to upload the files too.
     *
     * @param Asset|Traversable|array $files
     * @param string $collection
     * @param string $disk
     * @return Collection
     * @throws FileCannotBeAdded
     * @throws FileCannotBeAdded\DiskDoesNotExist
     * @throws FileCannotBeAdded\FileDoesNotExist
     * @throws FileCannotBeAdded\FileIsTooBig
     */
    private static function uploadMultiple($files, string $collection = 'default', string $disk = '')
    {
        $list = collect([]);
        collect($files)->each(function ($file) use ($list, $collection, $disk) {
            if ($file instanceof Asset) {
                $list->push($file);
            } else {
                $asset = new Asset();
                $asset->save();
                $list->push(self::uploadToAsset($file, $asset, null, false, $collection, $disk));
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
    public static function uploadFromBase64(string $file, string $filename, string $collection = 'default', string $disk = '')
    {
        return self::uploadBase64ToAsset($file, Asset::create(), $filename, $collection, $disk);
    }

    /**
     * Uploads the url by creating the
     * asset that is needed to upload the files too.
     *
     * @param string $url
     * @return Asset
     * @throws FileCannotBeAdded
     */
    public static function uploadFromUrl(string $url, string $collection = 'default', string $disk = '')
    {
        return self::uploadFromUrlToAsset($url, Asset::create(), $collection, $disk);
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param UploadedFile $file
     * @param Asset $asset
     * @param string|null $filename
     * @param bool $responsive
     * @param string $collection
     * @param string $disk
     * @return Asset
     * @throws FileCannotBeAdded
     */
    public static function uploadToAsset($file, $asset, $filename = null, bool $responsive = false, string $collection = 'default', string $disk = ''): Asset
    {
        if (! $file) {
            throw new InvalidArgumentException();
        }

        $fileAdd = $asset->addMedia($file);

        $fileAdd = self::prepareOptions($fileAdd, $filename);

        if($responsive) {
            $fileAdd = $fileAdd->withResponsiveImages();
        }

        $fileAdd->toMediaCollection($collection, $disk);

        return $asset->load('media');
    }

    /**
     * Uploads the given file to this instance of asset
     * and sets the dimensions as a custom property.
     *
     * @param string $file
     * @param Asset $asset
     * @param string|null $filename
     * @return Asset
     * @throws FileCannotBeAdded
     * @internal param $files
     */
    public static function uploadBase64ToAsset(string $file, $asset, string $filename, string $collection = 'default', string $disk = ''): Asset
    {
        $fileAdd = $asset->addMediaFromBase64($file);

        $fileAdd = self::prepareOptions($fileAdd, $filename);

        $fileAdd->toMediaCollection($collection, $disk);

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
    public static function uploadFromUrlToAsset(string $url, $asset, string $collection = 'default', string $disk = ''): Asset
    {
        $fileAdd = $asset->addMediaFromUrl($url);

        $filename = substr($url, strrpos($url, '/') + 1);
        $fileAdd->setName($filename);

        $fileAdd = self::prepareOptions($fileAdd, $filename);

        $fileAdd->toMediaCollection($collection, $disk);

        return $asset->load('media');
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
            $filename = substr($filename, 0, strrpos($filename, '.'));
            $filename = Str::slug($filename).'.'.$extension;

            return strtolower($filename);
        });

        return $fileAdd;
    }
}
