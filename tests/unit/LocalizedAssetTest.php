<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Application\AddAsset;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class LocalizedAssetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function tearDown(): void
    {
        Artisan::call('medialibrary:clear');

        parent::tearDown();
    }

    /** @test */
    public function it_can_get_a_localized_asset()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');
        app(AddAsset::class)->add($article, UploadedFile::fake()->image('imagefr.png'), 'banner', 'fr');

        $this->assertCount(2, $article->assetRelation()->get());
        $this->assertCount(1, $article->assets('banner', 'nl'));
        $this->assertCount(1, $article->assets('banner', 'fr'));

        $this->assertEquals('nl', $article->asset('banner', 'nl')->pivot->locale);
        $this->assertEquals('fr', $article->asset('banner', 'fr')->pivot->locale);
    }

    /** @test */
    public function it_can_get_a_localized_asset_by_current_locale()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');
        app(AddAsset::class)->add($article, UploadedFile::fake()->image('imagefr.png'), 'banner', 'fr');

        app()->setLocale('fr');
        $this->assertEquals('fr', $article->assets('banner')->first()->pivot->locale);
    }

    /** @test */
    public function asset_without_fallback_is_not_found()
    {
        config()->set('thinktomorrow.assetlibrary.use_fallback_locale', false);
        $article = $this->getArticleWithAsset('banner', 'nl');
        app(AddAsset::class)->add($article, UploadedFile::fake()->image('imagefr.png'), 'banner', 'fr');

        $this->assertEquals('', $article->asset('banner', 'en')->url());
    }

    /** @test */
    public function asset_with_fallback_locale_returns_fallback_asset_if_locale_not_found()
    {
        config()->set('thinktomorrow.assetlibrary.fallback_locale', 'nl');
        config()->set('thinktomorrow.assetlibrary.use_fallback_locale', true);

        $article = $this->getArticleWithAsset('banner', 'nl');

        $this->assertEquals('nl', $article->asset('banner', 'en')->pivot->locale);
    }

    /** @test */
    public function it_can_add_a_file_translation_for_default_locale()
    {
        $article = $this->getArticleWithAsset('banner', 'nl');
        app(AddAsset::class)->add($article, UploadedFile::fake()->image('imagefr.png'), 'banner', 'fr');

        $this->assertNotNull($article->asset('banner', 'fr'));
        $this->assertNotNull($article->asset('banner', 'nl'));
    }
}
