<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ReplaceMedia
{
    /**
     * Replace the media record of an asset.
     */
    public function handle(Media $originalMedia, Media $newMedia)
    {
        $newMedia->model_id = $originalMedia->model_id;
        $newMedia->save();

        $originalMedia->delete();
    }
}
