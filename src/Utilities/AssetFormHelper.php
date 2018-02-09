<?php

namespace Thinktomorrow\AssetLibrary\Utilities;

class AssetFormHelper
{
    /**
     * Generates the hidden field that links the file to a specific type.
     *
     * @param string $type
     * @param null $locale
     *
     * @param string $name
     * @return string
     */
    public static function typeField($type = '', $locale = null, $name = 'type'): string
    {
        $result = '<input type="hidden" value="'.$type.'" name="';

        if (! $locale) {
            return $result.$name.'">';
        }

        return $result.'trans['.$locale.'][files][]">';
    }

    /**
     * Generates the hidden field that links the file to translations.
     *
     * @param string $locale
     *
     * @return string
     */
    public static function localeField($locale = ''): string
    {
        return self::typeField($locale, null, 'locale');
    }
}
