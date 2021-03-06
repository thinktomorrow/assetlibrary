<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Exceptions\FileNotAccessibleException;
use Thinktomorrow\AssetLibrary\HasAsset;

class DeleteAsset
{
    /**
     * Removes an asset completely.
     *
     * @param $ids
     */
    public function delete($ids): void
    {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                self::remove($id);
            }
        } else {
            if (! $ids) {
                return;
            }
            self::remove($ids);
        }
    }

    public function remove($id)
    {
        if (! $id) {
            return false;
        }

        if (! $asset = Asset::find($id)) {
            return false;
        }

        $media = $asset->media;
        foreach ($media as $file) {
            if (! is_file(public_path($file->getUrl())) || ! is_writable(public_path($file->getUrl()))) {
                throw new FileNotAccessibleException();
            }
        }

        $asset->delete();
    }

    /**
     * Removes all assets completely.
     *
     * @param $ids
     */
    public function deleteAll(HasAsset $model): void
    {
        $model->assetRelation->each->delete();
    }
}
