<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Application\AssetUploader;
use Thinktomorrow\AssetLibrary\Application\ReplaceAsset;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class ReplaceAssetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function tearDown(): void
    {
        Artisan::call('media-library:clear');

        parent::tearDown();
    }

    /** @test */
    public function it_can_replace_an_asset_for_locale()
    {
        $article = $this->getArticleWithAsset('xxx');

        $this->assertCount(1, $article->assets('xxx'));

        app(ReplaceAsset::class)->handle($article, $article->assetRelation->first()->id, AssetUploader::upload(UploadedFile::fake()->image('newImage.png'))->id, 'xxx', 'nl');

        $this->assertCount(1, $article->refresh()->assets('xxx'));
        $this->assertCount(2, Asset::all());
        $this->assertEquals('/media/2/newimage.png', $article->asset('xxx')->url());
    }

    /** @test */
    public function it_can_replace_an_asset_with_specific_type()
    {
        $article = $this->getArticleWithAsset('custom-type');

        $this->assertCount(1, $assets = $article->assets('custom-type'));
        app(ReplaceAsset::class)->handle($article, $assets->first()->id, AssetUploader::upload(UploadedFile::fake()->image('newImage.png'))->id, 'custom-type', 'nl');

        $this->assertCount(1, $article->refresh()->assets('custom-type'));
        $this->assertEquals('/media/2/newimage.png', $article->asset('custom-type')->url());
    }

    /** @test */
    public function replacing_asset_for_type_doesnt_replace_other_assets_with_same_id()
    {
        $article = $this->getArticleWithAsset('custom-type', 'nl');

        app(AddAsset::class)->add($article, $article->asset('custom-type'), 'banner', 'nl');

        app(ReplaceAsset::class)->handle($article, $article->asset('custom-type')->id, AssetUploader::upload(UploadedFile::fake()->image('newImage.png'))->id, 'custom-type', 'nl');

        $this->assertCount(1, $article->refresh()->assets('custom-type'));
        $this->assertEquals('/media/2/newimage.png', $article->asset('custom-type')->url());
        $this->assertEquals('/media/2/newimage.png', $article->asset('custom-type')->url());
    }
}
