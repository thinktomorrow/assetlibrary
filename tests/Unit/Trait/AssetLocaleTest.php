<?php

namespace Thinktomorrow\AssetLibrary\Tests\Unit\Trait;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Application\CreateAsset;
use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetLocaleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

//    public function tearDown(): void
//    {
//        Artisan::call('media-library:clear');
//
//        parent::tearDown();
//    }

    public function test_it_can_get_an_asset_per_locale()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();
        $asset2 = $this->createAssetWithMedia('foobar.pdf');
        $model->assetRelation()->attach($asset, ['type' => 'doc', 'locale' => 'fr', 'order' => 1]);
        $model->assetRelation()->attach($asset2, ['type' => 'doc', 'locale' => 'en', 'order' => 0]);

        $this->assertEquals($asset2->id, $model->asset('doc', 'en')->id);
        $this->assertEquals($asset->id, $model->asset('doc', 'fr')->id);
    }

    public function test_it_can_get_same_asset_per_locale()
    {
        $model = Article::create();
        $asset = $this->createAssetWithMedia();
        $model->assetRelation()->attach($asset, ['type' => 'doc', 'locale' => 'fr', 'order' => 1]);
        $model->assetRelation()->attach($asset, ['type' => 'doc', 'locale' => 'en', 'order' => 0]);

        $this->assertCount(2, $model->assets('doc', null));
        $this->assertEquals($asset->id, $model->asset('doc', 'en')->id);
        $this->assertEquals($asset->id, $model->asset('doc', 'fr')->id);
    }

    public function test_it_can_get_assets_strict_by_locale()
    {
        config()->set('thinktomorrow.assetlibrary.fallback_locale', false);
        $model = $this->createModelWithAsset($this->createAssetWithMedia(), 'image', 'nl');

        $this->assertCount(0, $model->assets(null, 'en'));
        $this->assertCount(1, $model->assets(null, 'nl'));

        $this->assertNull($model->asset(null, 'en'));
        $this->assertInstanceOf(Asset::class, $model->asset(null, 'nl'));
        $this->assertInstanceOf(Asset::class, $model->asset(null, null));
    }

    public function test_it_can_get_an_asset_by_current_locale()
    {
        app()->setLocale('fr');

        $model = Article::create();
        $asset = $this->createAssetWithMedia();
        $asset2 = $this->createAssetWithMedia('foobar.pdf');
        $model->assetRelation()->attach($asset, ['type' => 'doc', 'locale' => 'fr', 'order' => 1]);
        $model->assetRelation()->attach($asset2, ['type' => 'doc', 'locale' => 'en', 'order' => 0]);

        $this->assertEquals($asset->id, $model->asset('doc')->id);
    }

    public function test_it_can_get_asset_by_fallback_locale()
    {
        config()->set('thinktomorrow.assetlibrary.fallback_locale', 'nl');
        $article = $this->createModelWithAsset($this->createAssetWithMedia(), 'banner', 'nl');

        $this->assertEquals('/media/1/image.png', $article->asset('banner', 'nl')->getUrl());
        $this->assertEquals('/media/1/image.png', $article->asset('banner', 'fr')->getUrl());
    }

    public function test_it_can_get_asset_by_app_fallback_locale()
    {
        config()->set('app.fallback_locale', 'nl');
        config()->set('thinktomorrow.assetlibrary.fallback_locale', null);
        $article = $this->createModelWithAsset($this->createAssetWithMedia(), 'banner', 'nl');

        $this->assertEquals('/media/1/image.png', $article->asset('banner', 'nl')->getUrl());
        $this->assertEquals('/media/1/image.png', $article->asset('banner', 'fr')->getUrl());
    }

    public function test_it_can_ignore_fallback_locale()
    {
        config()->set('thinktomorrow.assetlibrary.fallback_locale', false);
        $article = $this->createModelWithAsset($this->createAssetWithMedia(), 'banner', 'nl');

        $this->assertEquals('/media/1/image.png', $article->asset('banner', 'nl')->getUrl());
        $this->assertNull($article->asset('banner', 'fr'));
    }
}
