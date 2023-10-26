<?php

return [

    /**
     * Model types. Usually an asset is of type local but this can be anything.
     * The 'default' entry is required and is used in cases where no type is defined yet.
     *
     * In your project you can override this model with your own class.
     * Be sure to check it implements the Asset interface.
     */
    'types' => [
//        'default' => \Thinktomorrow\AssetLibrary\Asset::class,
        // e.g. 'vimeo' => \Project\Assets\VimeoAsset::class,
    ],

    /**
     * Each asset is added in a specific locale. With this setting to true,
     * you'll allow to fetch the asset with the given fallback locale
     * in case the asset is not found for the primary locale.
     *
     * If set to null, the projects app fallback locale is used.
     * If set to false, no fallback locale will be applied
     */
    'fallback_locale' => null,

    /**
     * Additional image formats.
     *
     * Images will always be converted with the original extension format.
     * Any formats you define here will be generated additionally.
     */
    'formats' => [
        'webp',
    ],

    /**
     * Available image conversions per format.
     */
    'conversions' => [
        'thumb' => [
            'width' => 150,
            'height' => 150,
        ],
        'small' => [
            'width' => 667,
            'height' => 667,
        ],
        'medium' => [
            'width' => 1024,
            'height' => 1024,
        ],
        'large' => [
            'width' => 1600,
            'height' => 1600,
        ],
        'full' => [
            'width' => 1920,
            'height' => 1920,
        ],
    ],

    /**
     * Set here the mimetypes for which there should be no conversions
     * This is the mimetype of the given original file. By default,
     * we omit svg and (animated) gif files since conversions
     * do not work as expected on these type of files.
     */
    'disable_conversions_for_mimetypes' => [
        'image/svg+xml',
        'image/gif',
    ],
];
