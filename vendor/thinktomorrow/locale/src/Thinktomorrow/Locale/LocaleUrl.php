<?php

namespace Thinktomorrow\Locale;

use Thinktomorrow\Locale\Parsers\RouteParser;
use Thinktomorrow\Locale\Parsers\UrlParser;

class LocaleUrl
{
    /**
     * @var Locale
     */
    private $locale;

    /**
     * @var UrlParser
     */
    private $urlparser;

    /**
     * @var null|string
     */
    private $placeholder;

    /**
     * @var RouteParser
     */
    private $routeparser;

    public function __construct(Locale $locale, UrlParser $urlparser, RouteParser $routeparser, $config = [])
    {
        $this->locale = $locale;
        $this->urlparser = $urlparser;
        $this->routeparser = $routeparser;

        $this->placeholder = isset($config['placeholder']) ? $config['placeholder'] : 'locale_slug';
    }

    /**
     * Generate a localized url.
     *
     * @param $url
     * @param null  $locale
     * @param array $parameters
     * @param null  $secure
     *
     * @return mixed
     */
    public function to($url, $locale = null, $parameters = [], $secure = null)
    {
        return $this->urlparser->set($url)
                            ->localize($locale)
                            ->parameters($parameters)
                            ->secure($secure)
                            ->get();
    }

    /**
     * Generate a localized route.
     * Note that unlike the Illuminate route() no parameter for 'absolute' path is available
     * since urls will always be rendered as absolute ones.
     *
     * @param $name
     * @param null  $locale
     * @param array $parameters
     *
     * @return mixed
     */
    public function route($name, $locale = null, $parameters = [])
    {
        // Locale should be passed as second parameter but in case it is passed as array
        // alongside other parameters, we will try to extract it
        if (!is_array($locale)) {
            $locale = [$this->placeholder => $locale];
        }

        $parameters = array_merge($locale, (array) $parameters);

        $locale = $this->extractLocaleFromParameters($parameters);

        return $this->routeparser->set($name)
                            ->localize($locale)
                            ->parameters($parameters)
                            ->get();
    }

    /**
     * Isolate locale value from parameters.
     *
     * @param array $parameters
     *
     * @return null|string
     */
    private function extractLocaleFromParameters(array &$parameters = [])
    {
        $locale = null;

        if (!array_key_exists($this->placeholder, $parameters)) {
            return $this->locale->get();
        }

        $locale = $this->locale->get($parameters[$this->placeholder]);

        // If locale parameter is not a 'real' parameter, we ignore this value and use the current locale instead
        // The 'wrong' parameter will be used without key
        if ($locale != $parameters[$this->placeholder]) {
            $parameters[] = $parameters[$this->placeholder];
        }

        unset($parameters[$this->placeholder]);

        return $locale;
    }
}
