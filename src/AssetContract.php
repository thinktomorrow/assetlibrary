<?php

namespace Thinktomorrow\AssetLibrary;

interface AssetContract
{
    public function hasData(string $key): bool;

    public function getData(string $key, $default = null);

    public function setData(string $name, $value): self;

    public function forgetData(string $name): self;

    /**
     * Proxy for the data values on the associated pivot. This is the context data
     * relevant and unique for each owner - asset relation.
     */
    public function hasPivotData(string $key): bool;

    /**
     * Proxy for the data values on the associated pivot. This is the context data
     * relevant and unique for each owner - asset relation.
     */
    public function getPivotData(string $key, $default = null);

    /**
     * Return path of the media file. In case the passed conversion
     * does not exist, the path to the original is returned.
     */
    public function getPath($conversionName = ''): ?string;

    /**
     * Return url of the media file. In case the passed conversion
     * does not exist, the url to the original is returned.
     */
    public function getUrl(string $conversionName = '', ?string $format = null): ?string;

    /**
     * Return filename of the media file. In case the passed conversion
     * does not exist, the name to the original is returned.
     */
    public function getFileName(string $conversionName = ''): ?string;

    public function getBaseName(string $conversionName = ''): string;

    /**
     * Checks if the conversion exists. It checks if file
     * exists as media record and on the server
     */
    public function exists(string $conversionName = ''): bool;

    public function getSize(): int;

    public function getHumanReadableSize(): string;

    public function getMimeType(): ?string;

    public function getExtension(): string;

    public function getExtensionType(): string;

    public function isImage(): bool;

    public function isVideo(): bool;

    public function getWidth(string $conversionName = ''): ?int;

    public function getHeight(string $conversionName = ''): ?int;
}
