<?php

namespace Thinktomorrow\Locale\Parsers;

interface Parser
{
    /**
     * Set the base url or routename.
     *
     * @param $url
     *
     * @return mixed
     */
    public function set($url);

    /**
     * Retrieve the generated / altered url.
     *
     * @return mixed
     */
    public function get();

    /**
     * Place locale segment in front of url path
     * e.g. /foo/bar is transformed into /en/foo/bar.
     *
     * @param null $locale
     *
     * @return string
     */
    public function localize($locale = null);

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function parameters(array $parameters = []);

    /**
     * @param bool $secure
     *
     * @return $this
     */
    public function secure($secure = true);
}
