<?php

namespace Thinktomorrow\AssetLibrary\Application;

use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Thinktomorrow\AssetLibrary\AbstractAsset;
use Thinktomorrow\AssetLibrary\AssetContract;
use Thinktomorrow\AssetLibrary\AssetHelper;

class CreateAsset
{
    private ?string $filename = null;
    private bool $removeOriginal = false;

    // Available input types
    private ?UploadedFile $uploadedFile = null;
    private ?string $url = null;
    private ?string $path = null;
    private ?string $base64 = null;

    public function uploadedFile(UploadedFile $file): static
    {
        $this->uploadedFile = $file;

        return $this;
    }

    public function path(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function base64(string $base64): static
    {
        $this->base64 = $base64;

        return $this;
    }


    public function filename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function removeOriginal(): static
    {
        $this->removeOriginal = true;

        return $this;
    }

    public function save(string $disk = '', string $assetType = 'default'): AssetContract
    {
        if (! $assetClass = config('thinktomorrow.assetlibrary.types.' . $assetType)) {
            throw new \InvalidArgumentException('Passed asset type "'.$assetType.'" is not found as available asset type in the assetlibrary.types config.');
        }

        $asset = $assetClass::create();

        $fileAdder = $this->getFileAdder($asset)->preservingOriginal(! $this->removeOriginal);

        if ($this->filename) {
            $extension = AssetHelper::getExtension($this->filename) ?: $this->guessExtension();

            $fileAdder->usingFileName(AssetHelper::getBaseName($this->filename) . ($extension ? '.' . $extension : ''));
            $fileAdder->usingName(AssetHelper::getBaseName($this->filename));
        }

        $fileAdder->toMediaCollection(AbstractAsset::MEDIA_COLLECTION, $disk);

        return $asset->load('media');
    }

    private function guessExtension(): ?string
    {
        if ($this->uploadedFile) {
            return $this->uploadedFile->getClientOriginalExtension();
        }
        if ($this->path) {
            return AssetHelper::getExtension($this->path);
        }
        if ($this->url) {
            return AssetHelper::getExtension($this->url);
        }

        // TODO: extract extension from base64.
        return null;
    }

    private function getFileAdder(AssetContract $asset): FileAdder
    {
        foreach ([
             'uploadedFile' => 'addMedia',
             'path' => 'addMedia',
             'url' => 'addMediaFromUrl',
             'base64' => 'addMediaFromBase64',
         ] as $inputProperty => $fileAdderMethod) {
            if ($this->{$inputProperty}) {
                return $asset->{$fileAdderMethod}($this->{$inputProperty});
            }
        }

        throw new \InvalidArgumentException('File input is missing. You should call one of the methods: path(), url() or base64().');
    }
}
