<?php

namespace Thinktomorrow\AssetLibrary\Test;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Thinktomorrow\AssetLibrary\Models\Asset;
use Thinktomorrow\AssetLibrary\Models\AssetUploader;

class AssetUploadTest extends TestCase
{
    /**
     * @test
     *
     * This test currently doesn't fail if we set keepOriginal to false TODO FIX THIS.
     */
    public function it_can_keep_original_source()
    {
        $source = UploadedFile::fake()->create('testSource.txt');

        // Second parameter is flag to preserve original source file
        $asset = AssetUploader::upload($source, null, true);

        $this->assertFileExists($source->getPath());

        // Cleanup
        $asset->delete(); // remove uploaded asset
    }
}
