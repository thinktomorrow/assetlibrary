<?php

use Thinktomorrow\Locale\LocaleUrl;

if (!function_exists('localeurl')) {
    /**
     * @param $url
     * @param null  $locale
     * @param array $extra
     * @param null  $secure
     *
     * @return mixed
     */
    function localeurl($url, $locale = null, $extra = [], $secure = null)
    {
        return app(LocaleUrl::class)->to($url, $locale, $extra, $secure);
    }
}

if (!function_exists('localeroute')) {
    /**
     * @param $name
     * @param null  $locale
     * @param array $parameters
     * @param bool  $absolute
     *
     * @return
     */
    function localeroute($name, $locale = null, $parameters = [], $absolute = true)
    {
        return app(LocaleUrl::class)->route($name, $locale, $parameters, $absolute);
    }
}
