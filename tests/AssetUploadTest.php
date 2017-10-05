<?php

namespace Thinktomorrow\AssetLibrary\Test;

use Illuminate\Http\File;
use Thinktomorrow\AssetLibrary\Models\Asset;

class AssetUploadTest extends TestCase
{
    /** @test */
    public function it_can_keep_original_source()
    {
        $source = __DIR__.'/temp/fakeSource.txt';
        touch($source);

        // Second parameter is flag to preserve original source file
        $asset = Asset::upload(new File($source), true);

        $this->assertTrue(file_exists($source));

        // Cleanup
        unlink($source); // remove original
        $asset->delete(); // remove uploaded asset
    }
}
