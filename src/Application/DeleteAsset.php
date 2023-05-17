<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Asset;

class DeleteAsset
{
    public function handle(Asset $asset): void
    {
        DB::table('assets_pivot')->where('asset_id', $asset->id)->delete();

        // Associated media and the files on disk will be deleted as well.
        $asset->delete();
    }
}
