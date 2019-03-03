<?php

namespace Thinktomorrow\AssetLibrary\Models;

use Illuminate\Support\Collection;
use Thinktomorrow\AssetLibrary\Models\Asset;

class AssetLibrary
{
    /**
     * Removes one or more assets by their ids.
     */
    public static function removeByIds($imageIds)
    {
        if (is_array($imageIds)) {
            foreach ($imageIds as $id) {
                self::remove($id);
            }
        } else {
            if (! $imageIds) {
                return;
            }

            self::remove($imageIds);
        }
    }

    /**
     * Removes one assets by id.
     * It also checks if you have the permissions to remove the file.
     *
     * @param $imageIds
     */
    public static function remove($id)
    {
        if(!$id) return false;

        $asset = Asset::find($id)->first();
        $media = $asset->media;

        foreach ($media as $file) {
            if (! is_file(public_path($file->getUrl())) || ! is_writable(public_path($file->getUrl()))) {
                return;
            }
        }

        $asset->delete();
    }

    /**
     * Returns a collection of all the assets in the library.
     * @return \Illuminate\Support\Collection
     */
    public static function getAllAssets(): Collection
    {
        return Asset::all()->sortByDesc('created_at');
    }
}
