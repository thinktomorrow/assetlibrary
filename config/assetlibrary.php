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
        'small' => [
            'width'     => 667,
            'height'    => 667,
        ],
        'medium' => [
            'width'     => 1024,
            'height'    => 1024,
        ],
        'large' => [
            'width'     => 1600,
            'height'    => 1600,
        ],
        'full' => [
            'width'     => 1920,
            'height'    => 1920,
        ],
    ],

    /**
     * Additional conversion formats.
     * Conversions will always be generated in the original format.
     * Any formats you define here will be generated additionally.
     */
    'formats' => [],

    'allowCropping' => false,
];
