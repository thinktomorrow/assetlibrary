<?php

namespace Thinktomorrow\AssetLibrary\Tests\Unit\Model;

use Thinktomorrow\AssetLibrary\Asset;
use Thinktomorrow\AssetLibrary\AssetType\AssetTypeFactory;
use Thinktomorrow\AssetLibrary\AssetType\NotFoundAssetType;
use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\stubs\VimeoAsset;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssetTypeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();

        config()->set('thinktomorrow.assetlibrary.conversions', [
            'thumb' => [
                'width' => 50,
                'height' => 50,
            ],
        ]);

        config()->set('thinktomorrow.assetlibrary.types', [
            'default' => Asset::class,
            'vimeo' => VimeoAsset::class,
        ]);
    }

    public function test_it_can_create_instance()
    {
        $model = AssetTypeFactory::instance('default', ['foo' => 'bar']);
        $this->assertInstanceOf(Asset::class, $model);

        $model = AssetTypeFactory::instance('vimeo', ['foo' => 'bar']);
        $this->assertInstanceOf(VimeoAsset::class, $model);
    }

    public function test_it_adds_asset_type_on_instantiation()
    {
        $model = AssetTypeFactory::instance('default', ['foo' => 'bar']);
        $this->assertEquals('default', $model->asset_type);

        $model = AssetTypeFactory::instance('vimeo', ['foo' => 'bar']);
        $this->assertEquals('vimeo', $model->asset_type);
    }

    /** @test */
    public function it_creates_model_based_on_asset_type()
    {
        $instance = Asset::create(['asset_type' => 'default']);
        $this->assertInstanceOf(Asset::class, $instance);

        $instance = Asset::create(['asset_type' => 'vimeo']);
        $this->assertInstanceOf(VimeoAsset::class, $instance);

        $instance = VimeoAsset::create();
        $this->assertInstanceOf(VimeoAsset::class, $instance);
    }

    /** @test */
    public function it_returns_expected_instance_on_eloquent_find()
    {
        $instanceLocal = Asset::create(['asset_type' => 'default']);
        $instanceVimeo = Asset::create(['asset_type' => 'vimeo']);

        $this->assertInstanceOf(Asset::class, Asset::find($instanceLocal->id));
        $this->assertInstanceOf(Asset::class, VimeoAsset::find($instanceLocal->id));
        $this->assertInstanceOf(VimeoAsset::class, Asset::find($instanceVimeo->id));
        $this->assertInstanceOf(VimeoAsset::class, VimeoAsset::find($instanceVimeo->id));

        $this->assertInstanceOf(Asset::class, Asset::findOrFail($instanceLocal->id));
        $this->assertInstanceOf(Asset::class, VimeoAsset::findOrFail($instanceLocal->id));
        $this->assertInstanceOf(VimeoAsset::class, Asset::findOrFail($instanceVimeo->id));
        $this->assertInstanceOf(VimeoAsset::class, VimeoAsset::findOrFail($instanceVimeo->id));

        $this->assertInstanceOf(Asset::class, Asset::findMany([$instanceLocal->id])->first());
        $this->assertInstanceOf(Asset::class, VimeoAsset::findMany([$instanceLocal->id])->first());
        $this->assertInstanceOf(VimeoAsset::class, Asset::findMany([$instanceVimeo->id])->first());
        $this->assertInstanceOf(VimeoAsset::class, VimeoAsset::findMany([$instanceVimeo->id])->first());

        $this->assertInstanceOf(Asset::class, Asset::where('id', $instanceLocal->id)->first());
        $this->assertInstanceOf(Asset::class, VimeoAsset::where('id', $instanceLocal->id)->first());
        $this->assertInstanceOf(VimeoAsset::class, Asset::where('id', $instanceVimeo->id)->first());
        $this->assertInstanceOf(VimeoAsset::class, VimeoAsset::where('id', $instanceVimeo->id)->first());
    }

    /** @test */
    public function it_returns_expected_instance_on_eloquent_relations()
    {
        $model = $this->createModelWithAsset(VimeoAsset::create());

        $this->assertInstanceOf(VimeoAsset::class, $model->assetRelation()->first());
    }

    /** @test */
    public function it_returns_expected_instance_on_multiple_eloquent_relations()
    {
        $model = $this->createModelWithAsset(VimeoAsset::create());
        $model->assetRelation()->attach(Asset::create(), ['type' => 'video', 'locale' => 'nl', 'order' => 1]);

        $this->assertCount(2, $model->assetRelation()->get());
        $this->assertInstanceOf(VimeoAsset::class, $model->assetRelation()->get()[0]);
        $this->assertInstanceOf(Asset::class, $model->assetRelation()->get()[1]);
    }

    /** @test */
    public function it_throws_exception_when_asset_type_cannot_be_found()
    {
        $this->expectException(NotFoundAssetType::class);

        Asset::create(['asset_type' => 'xxx']);
    }

    public function test_it_can_get_asset()
    {
        $model = $this->createModelWithAsset(VimeoAsset::create());

        $this->assertInstanceOf(VimeoAsset::class, $model->asset(null, null));
    }

    public function test_it_can_get_assets_per_type()
    {
        $model = $this->createModelWithAsset(VimeoAsset::create());
        $model->assetRelation()->attach(Asset::create(), ['type' => 'video', 'locale' => 'nl', 'order' => 1]);

        $this->assertCount(2, $model->assets(null, null));
        $this->assertInstanceOf(VimeoAsset::class, $model->assets(null, null)[0]);
        $this->assertInstanceOf(Asset::class, $model->assets(null, null)[1]);
    }
}
