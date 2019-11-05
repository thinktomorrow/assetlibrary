<?php

namespace Thinktomorrow\AssetLibrary\Tests\unit;

use Illuminate\Support\Facades\Artisan;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class NullAssetTest extends TestCase
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
    public function url_returns_empty_string_when_asset_doesnt_exist()
    {
        $article = Article::create();

        $this->assertEquals('', $article->asset('banner')->url());
    }

    /** @test */
    public function filename_returns_empty_string_when_asset_doesnt_exist()
    {
        $article = Article::create();

        $this->assertEquals('', $article->asset('banner')->filename());
    }

    /** @test */
    public function it_can_check_if_an_asset_exists()
    {
        $article = Article::create();

        $this->assertEquals(false, $article->asset('banner')->exists());
    }

    
}
