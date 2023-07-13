<?php

namespace Thinktomorrow\AssetLibrary\Tests\Unit\Model;

use Thinktomorrow\AssetLibrary\Tests\stubs\Article;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class AssociatedAssetDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Article::migrate();
    }

    public function test_it_returns_null_when_custom_data_does_not_exist()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());

        $this->assertNull($model->asset('image')->getPivotData('unknown'));
        $this->assertFalse($model->asset('image')->hasPivotData('unknown'));
    }

    public function test_it_returns_default_when_custom_data_does_not_exist()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());

        $this->assertEquals('DEFAULT', $model->asset('image')->getPivotData('unknown', 'DEFAULT'));
    }

    public function test_it_can_set_custom_data()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());
        $model->asset('image')->pivot->setData('foo', 'bar');

        $this->assertEquals('bar', $model->asset('image')->getPivotData('foo'));
        $this->assertTrue($model->asset('image')->hasPivotData('foo'));
    }

    public function test_it_can_forget_custom_data()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());

        $model->asset('image')->pivot->setData('foo', 'bar');
        $this->assertEquals('bar', $model->asset('image')->getPivotData('foo'));
        $this->assertTrue($model->asset('image')->hasPivotData('foo'));

        $model->asset('image')->pivot->forgetData('foo');
        $this->assertNull($model->asset('image')->getPivotData('foo'));
        $this->assertFalse($model->asset('image')->hasPivotData('foo'));
    }

    public function test_it_can_save_custom_data()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());

        $model->asset('image')->pivot->setData('foo', 'bar');
        $model->asset('image')->save();

        $this->assertEquals('bar', $model->asset('image')->getPivotData('foo'));
    }

    public function test_it_can_forget_saved_custom_data()
    {
        $model = $this->createModelWithAsset($this->createAssetWithMedia());

        $model->asset('image')->pivot->setData('foo', 'bar');
        $model->asset('image')->save();

        $this->assertEquals('bar', $model->asset('image')->getPivotData('foo'));

        $model->asset('image')->pivot->forgetData('foo');
        $model->asset('image')->save();

        $this->assertNull($model->asset('image')->getPivotData('foo'));
    }
}
