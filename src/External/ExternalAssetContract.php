<?php

namespace Thinktomorrow\AssetLibrary\External;

use Thinktomorrow\AssetLibrary\AssetContract;

/**
 * When Asset refers to an external media, hosted on a third party location (e.g. vimeo, cloudinary, youtube, ...)
 * The preview values refer to the local media which acts as a visual representation in the admin environment.
 */
interface ExternalAssetContract extends AssetContract
{
    public function getExternalId(): string;

    public function getExternalRootUrl(): string;

    public function getPreviewPath($conversionName = ''): ?string;

    public function getPreviewUrl(string $conversionName = '', ?string $format = null): ?string;

    public function getPreviewFileName(string $conversionName = ''): ?string;

    public function getPreviewBaseName(string $conversionName = ''): string;

    public function previewExists(string $conversionName = ''): bool;

    public function getPreviewSize(): int;

    public function getPreviewHumanReadableSize(): string;

    public function getPreviewMimeType(): ?string;

    public function getPreviewExtension(): string;

    public function getPreviewExtensionType(): string;

    public function isPreviewImage(): bool;

    public function isPreviewVideo(): bool;

    public function getPreviewWidth(string $conversionName = ''): ?int;

    public function getPreviewHeight(string $conversionName = ''): ?int;
}
