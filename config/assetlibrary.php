<?php

return [

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
     * Available conversions.
     */
    'conversions' => [
        'thumb' => [
            'width'     => 150,
            'height'    => 150,
        ],
        'medium' => [
            'width'     => 300,
            'height'    => 130,
        ],
        'large' => [
            'width'     => 1024,
            'height'    => 353,
        ],
        'full' => [
            'width'     => 1600,
            'height'    => 553,
        ],
    ],

    'allowCropping' => false,
];
