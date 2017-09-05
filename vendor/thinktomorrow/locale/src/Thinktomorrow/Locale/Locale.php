<?php

namespace Thinktomorrow\Locale;

use Illuminate\Http\Request;

class Locale
{
    private $request;
    private $available_locales;
    private $hidden_locale;
    private $fallback_locale;
    private $query_key;

    public function __construct(Request $request, $config)
    {
        $this->request = $request;

        $this->available_locales = (array) $config['available_locales'];
        $this->hidden_locale = isset($config['hidden_locale']) ? $config['hidden_locale'] : null;
        $this->fallback_locale = (isset($config['fallback_locale']) && $config['fallback_locale']) ? $config['fallback_locale'] : config('app.fallback_locale');
        $this->query_key = isset($config['query_key']) ? $config['query_key'] : null;
    }

    /**
     * Define the list of available locales.
     * This way the developer can set his own curated list of locales.
     *
     * @param array $locales
     * @return $this
     */
    public function setAvailables(array $locales)
    {
        $this->available_locales = $locales;

        return $this;
    }

    /**
     * Setup the locale for current request and
     * get the locale slug for the route.
     *
     * @param null $locale
     *
     * @return null|string
     */
    public function set($locale = null)
    {
        $this->store($locale);

        return $this->getSlug();
    }

    /**
     * Get the current locale.
     *
     * @param null $locale
     *
     * @return null|string
     */
    public function get($locale = null)
    {
        return $this->validateLocale($locale) ? $locale : app()->getLocale();
    }

    /**
     * Get default locale which is useful for providing locale fallback
     * TODO: change fallback locale so that it uses the app.locale instead
     *
     * @return null|string
     */
    public static function getDefault(): string
    {
        return config('app.locale');
    }

    /**
     * Retrieve the url slug for current or passed locale.
     *
     * @param null $locale
     *
     * @return null|string
     */
    public function getSlug($locale = null)
    {
        $locale = $this->get($locale);

        if ($this->hidden_locale == $locale) {
            return;
        }

        return $locale;
    }

    /**
     * Check if current or passed locale is set as hidden.
     *
     * @param null $locale
     *
     * @return bool
     */
    public function isHidden($locale = null)
    {
        // If a specific locale string is passed we first validate it represents a valid locale
        if ($locale && !$this->validateLocale($locale)) {
            return false;
        }

        $locale = $this->get($locale);

        return $this->hidden_locale == $locale;
    }

    /**
     * Set locale according to following priority:.
     *
     * 0) If locale is passed as parameter, this locale will be forced
     * 1) If locale is in request as query parameter e.g. ?lang=fr,
     * 2) If locale is in request url (subdomain, domain or segment) eg. nl.example.com, example.nl or example.com/nl
     * 3) Default: get locale from cookie
     * 4) Otherwise: set locale to our fallback language
     *
     * @param null $locale
     *
     * @return array|mixed|null|string
     */
    private function store($locale = null)
    {
        if (!$locale or !in_array($locale, $this->available_locales)) {
            $locale = $this->fallback_locale;

            if ($this->validateLocale($this->request->cookie('locale'))) {
                $locale = $this->request->cookie('locale');
            }

            if ($locale_from_url = $this->getLocaleFromUrl()) {
                $locale = $locale_from_url;
            }

            if ($this->query_key && ($queryLocale = $this->request->get($this->query_key)) && $this->validateLocale($queryLocale)) {
                $locale = $queryLocale;
            }
        }

        app()->setlocale($locale);

        return $locale;
    }

    /**
     * @return bool|string
     */
    private function getLocaleFromUrl()
    {
        if ($locale = $this->getLocaleSegment()) {
            return $locale;
        }

        // At this point is means the url does not contain a specific locale so
        // it is assumed the hidden locale is in effect
        if ($locale = $this->hidden_locale) {
            return $locale;
        }

        return false;
    }

    private function getLocaleSegment()
    {
        $segment = $this->request->segment(1);

        return ($this->validateLocale($segment)) ? $segment : false;
    }

    /**
     * @param null $locale
     *
     * @return bool
     */
    private function validateLocale($locale = null)
    {
        if (!$locale) {
            return false;
        }

        return in_array($locale, $this->available_locales);
    }
}
