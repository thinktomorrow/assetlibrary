<?php

namespace Thinktomorrow\AssetLibrary\Tests\Application;

use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Thinktomorrow\AssetLibrary\Application\CreateAsset;
use Thinktomorrow\AssetLibrary\Tests\TestCase;

class CreateExternalAssetTest extends TestCase
{
    public function test_it_can_create_asset_from_uploaded_file()
    {
        $asset = (new CreateAsset())->uploadedFile(UploadedFile::fake()->image('test-image.gif'))->save();

        $this->assertNotNull($asset);
        $this->assertEquals('/media/1/test-image.gif', $asset->getUrl());
        $this->assertEquals('/media/1/conversions/test-image-thumb.gif', $asset->getUrl('thumb'));
    }

    public function test_it_can_create_asset_from_path()
    {
        $asset = (new CreateAsset())->path(__DIR__.'/../media-stubs/foobar.pdf')->save();

        $this->assertNotNull($asset);
        $this->assertEquals('/media/1/foobar.pdf', $asset->getUrl());
    }

    public function test_it_can_create_asset_from_url()
    {
        $asset = (new CreateAsset())->url('https://getchief.be/storage/1/conversions/screenshot-2023-01-18-at-15-thumb.png')->save();

        $this->assertNotNull($asset);
        $this->assertEquals('/media/1/screenshot-2023-01-18-at-15-thumb.png', $asset->getUrl());
    }

    public function test_it_can_create_asset_from_base64()
    {
        $asset = (new CreateAsset())->base64($this->dummyBase64Payload())->save();

        $this->assertNotNull($asset);
        $this->assertNotNull($asset->getUrl());
    }

    public function test_it_requires_at_least_one_input()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new CreateAsset())->save();
    }

    public function test_it_throws_error_if_path_is_invalid()
    {
        $this->expectException(FileDoesNotExist::class);

        (new CreateAsset())->path('fake')->save();
    }

    public function test_it_creates_image_asset_with_all_conversions()
    {
        $asset = (new CreateAsset())->path(__DIR__.'/../media-stubs/image.png')->save();

        $this->assertEquals('/media/1/image.png', $asset->getUrl());
        $this->assertEquals('/media/1/conversions/image-full.png', $asset->getUrl('full'));
        $this->assertEquals('/media/1/conversions/image-thumb.png', $asset->getUrl('thumb'));
    }

    public function test_it_can_set_filename_for_uploaded_file()
    {
        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->filename('updated-name')
            ->save();

        $this->assertEquals('updated-name.gif', $asset->getFileName());
        $this->assertEquals('updated-name-thumb.gif', $asset->getFileName('thumb'));
    }

    public function test_it_can_set_filename_with_extension_for_uploaded_file()
    {
        $asset = (new CreateAsset())
            ->uploadedFile(UploadedFile::fake()->image('test-image.gif'))
            ->filename('updated-name.jpg')
            ->save();

        $this->assertEquals('image/gif', $asset->getMimeType());
        $this->assertEquals('updated-name.jpg', $asset->getFileName());
        $this->assertEquals('updated-name-thumb.jpg', $asset->getFileName('thumb'));
    }

    public function test_it_can_set_filename_for_path()
    {
        $asset = (new CreateAsset())
            ->path(__DIR__.'/../media-stubs/image.png')
            ->filename('updated-name')
            ->save();

        $this->assertEquals('updated-name.png', $asset->getFileName());
        $this->assertEquals('updated-name-thumb.png', $asset->getFileName('thumb'));
    }

    public function test_it_can_set_filename_with_extension_for_path()
    {
        $asset = (new CreateAsset())
            ->path(__DIR__.'/../media-stubs/image.png')
            ->filename('updated-name.jpg')
            ->save();

        $this->assertEquals('updated-name.jpg', $asset->getFileName());
        $this->assertEquals('updated-name-thumb.jpg', $asset->getFileName('thumb'));
    }

    public function test_it_can_set_filename_for_url()
    {
        $asset = (new CreateAsset())->url('https://getchief.be/storage/1/conversions/screenshot-2023-01-18-at-15-thumb.png')
            ->filename('updated-name')
            ->save();

        $this->assertEquals('updated-name.png', $asset->getFileName());
        $this->assertEquals('updated-name-thumb.png', $asset->getFileName('thumb'));
    }

    public function test_it_can_set_filename_with_extension_for_url()
    {
        $asset = (new CreateAsset())->url('https://getchief.be/storage/1/conversions/screenshot-2023-01-18-at-15-thumb.png')
            ->filename('updated-name.jpg')
            ->save();

        $this->assertEquals('updated-name.jpg', $asset->getFileName());
        $this->assertEquals('updated-name-thumb.jpg', $asset->getFileName('thumb'));
    }

    public function test_it_can_set_filename_for_base64()
    {
        $asset = (new CreateAsset())
            ->base64($this->dummyBase64Payload())
            ->filename('updated-name')
            ->save();

        $this->assertEquals('updated-name', $asset->getFileName());
        $this->assertEquals('updated-name-thumb.jpg', $asset->getFileName('thumb'));
    }

    public function test_it_can_set_filename_with_extension_for_base64()
    {
        $asset = (new CreateAsset())
            ->base64($this->dummyBase64Payload())
            ->filename('updated-name.jpg')
            ->save();

        $this->assertEquals('updated-name.jpg', $asset->getFileName());
        $this->assertEquals('updated-name-thumb.jpg', $asset->getFileName('thumb'));
    }

    public function test_it_has_no_problem_with_upper_case_extentions()
    {
        $asset = (new CreateAsset())
            ->path(__DIR__.'/../media-stubs/image-with-uppercased-extension.PNG')
            ->save();

        $this->assertEquals('image/png', $asset->getMimeType());
        $this->assertEquals('image-with-uppercased-extension.PNG', $asset->getFileName());
        $this->assertEquals('image-with-uppercased-extension-thumb.PNG', $asset->getFileName('thumb'));
    }

    public function test_it_can_opt_to_remove_original()
    {
        copy(__DIR__.'/../media-stubs/foobar.pdf', __DIR__.'/../media-stubs/foobar-copied.pdf');

        $asset = (new CreateAsset())
            ->path(__DIR__.'/../media-stubs/foobar-copied.pdf')
            ->removeOriginal()
            ->save();

        $this->assertEquals('/media/1/foobar-copied.pdf', $asset->getUrl());

        $this->assertFileDoesNotExist(__DIR__.'/../media-stubs/foobar-copied.pdf');
    }

    public function test_it_can_save_to_custom_disk()
    {
        $asset = (new CreateAsset())->path(__DIR__.'/../media-stubs/foobar.pdf')
            ->save('secondMediaDisk');

        $this->assertNotNull($asset);
        $this->assertEquals('/media2/1/foobar.pdf', $asset->getUrl());
    }
}
