<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Thinktomorrow\AssetLibrary\Application\DeleteAsset;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Exceptions\FileNotAccessibleException;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\stubs\ArticleWithSoftdelete;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class DeleteAssetTest extends TestCase
{
    public function tearDown(): void
    {
        Artisan::call('media-library:clear');

        parent::tearDown();
    }

    public function test_it_can_remove_an_asset()
    {
        $article = $this->createModelWithAsset('xxx');

        $this->assertCount(1, $article->assets('xxx'));

        app(DeleteAsset::class)->delete($article->assetRelation->first()->id);

        $this->assertCount(0, Article::first()->assets());
    }


    /**
     * @test
     */
    public function it_can_remove_itself()
    {
        //upload a single image
        $asset = $this->createAssetWithMedia();

        $this->assertEquals($asset->filename(), 'image.png');
        $this->assertEquals($asset->url(), '/media/1/image.png');
        $this->assertFileExists(public_path($asset->url()));

        $filepath = $asset->url();
        $asset->delete();

        $this->assertFileDoesNotExist(public_path($filepath));
        $this->assertCount(0, Asset::all());
    }

    /**
     * @test
     */
    public function it_can_remove_an_image()
    {
        //upload a single image
        $asset = $this->createAssetWithMedia();

        $this->assertEquals($asset->filename(), 'image.png');
        $this->assertEquals($asset->url(), '/media/1/image.png');

        $asset2 = $this->createAssetWithMedia('image.png');

        $this->assertEquals($asset2->filename(), 'image.png');
        $this->assertEquals($asset2->url(), '/media/2/image.png');

        app(DeleteAsset::class)->delete($asset->id);

        $this->assertEquals(1, Asset::all()->count());
        $this->assertEquals($asset2->id, Asset::all()->first()->id);
    }

    /**
     * @test
     */
    public function it_can_handle_invalid_inputs_to_remove_function()
    {
        $asset = $this->createAssetWithMedia();

        $this->assertEquals($asset->filename(), 'image.png');
        $this->assertEquals($asset->url(), '/media/1/image.png');

        app(DeleteAsset::class)->delete([null]);
        app(DeleteAsset::class)->delete(null);

        $this->assertEquals(1, Asset::all()->count());
    }

    /**
     * @test
     */
    public function it_can_remove_multiple_images()
    {
        //upload a single image
        $asset = $this->createAssetWithMedia();

        $this->assertEquals($asset->filename(), 'image.png');
        $this->assertEquals($asset->url(), '/media/1/image.png');

        $asset2 = $this->createAssetWithMedia('image.png');

        $this->assertEquals($asset2->filename(), 'image.png');
        $this->assertEquals($asset2->url(), '/media/2/image.png');

        app(DeleteAsset::class)->delete([$asset->id, $asset2->id]);

        $this->assertEquals(0, Asset::all()->count());
    }

    /** @test */
    public function softdeleting_model_will_set_pivot_to_unused()
    {
        ArticleWithSoftdelete::migrate();
        $article = $this->getSoftdeleteArticleWithAsset('banner');

        $article->delete();

        $this->assertEquals(1, DB::table('asset_pivots')->get()->first()->unused);
    }

    /**
     * @test
     */
    public function it_doesnt_remove_the_asset_if_you_dont_have_permissions()
    {
        $this->expectException(FileNotAccessibleException::class);

        //upload a single image
        $asset = $this->createAssetWithMedia();
        $dir   = public_path($asset->url());

        @chmod($dir, 0444);

        $this->assertFileExists($dir);
        $this->assertFileIsReadable($dir);
        $this->assertFileIsNotWritable($dir);

        app(DeleteAsset::class)->delete($asset->id);

        $this->assertEquals(1, Asset::all()->count());
        $this->assertCount(1, $asset->fresh()->media);

        @chmod($dir, 0777);
        app(DeleteAsset::class)->delete($asset->id);
    }
}
