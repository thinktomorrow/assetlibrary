<?php

return [

    /**
     * Each asset is added in a specific locale. With this setting to true,
     * you'll allow to fetch the asset with the given fallback locale
     * in case the asset is not found for the primary locale.
     */
    'use_fallback_locale' => true,

    /**
     * Which fallback locale is used.
     * If set to null, the default app fallback locale is used instead.
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
            'formats' => [
                'jpg','png',
            ],
        ],
    ],

    /**
     * Which formats should be included in the conversions by default.
     * Note that the original format will always be used in the conversions.
     */
    'available_formats' => [
        'webp',
    ],

    /**
     * If the format parameter is not explicitly set when retrieving the media url, the default
     * format is used. Options are: original or webp.
     */
    'default_format_choice' => 'original',

    'allowWebP' => false,

    'allowCropping' => false,
];
