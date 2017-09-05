<?php

namespace Thinktomorrow\Locale\Parsers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator;

class RouteParser implements Parser
{
    /**
     * @var UrlParser
     */
    private $parser;

    /**
     * @var Translator
     */
    private $translator;

    private $name;
    private $parameters = [];
    private $localeslug;

    public function __construct(UrlParser $parser, Translator $translator)
    {
        $this->parser = $parser;
        $this->translator = $translator;
    }

    /**
     * Set the routename.
     *
     * @param $name
     *
     * @return mixed
     */
    public function set($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Retrieve the generated / altered url
     * If no translated routekey is found, it means the route itself does not need to be
     * translated and we allow the native url generator to deal with the route generation.
     *
     * @return mixed
     */
    public function get()
    {
        $routekey = $this->translator->get('routes.'.$this->name, [], $this->localeslug);

        $uri = ($routekey === 'routes.'.$this->name)
                ? $this->parser->resolveRoute($this->name, $this->parameters)
                : $this->replaceParameters($routekey, $this->parameters);

        return $this->parser->set($uri)->get();
    }

    /**
     * Place locale segment in front of url path
     * e.g. /foo/bar is transformed into /en/foo/bar.
     *
     * @param null $localeslug
     *
     * @return string
     */
    public function localize($localeslug = null)
    {
        $this->localeslug = $localeslug;
        $this->parser->localize($localeslug);

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function parameters(array $parameters = [])
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @param bool $secure
     *
     * @return $this
     */
    public function secure($secure = true)
    {
        $this->parser->secure($secure);

        return $this;
    }

    /**
     * Replace route parameters.
     *
     * @param $uri
     * @param array $parameters
     *
     * @return mixed|string
     */
    protected function replaceParameters($uri, $parameters = [])
    {
        $parameters = (array) $parameters;

        $uri = $this->replaceRouteParameters($uri, $parameters);
        $uri = str_replace('//', '/', $uri);

        return $uri;
    }

    /**
     * Replace all of the wildcard parameters for a route path.
     *
     * @note: based on the Illuminate\Routing\UrlGenerator code
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceRouteParameters($path, array $parameters)
    {
        $path = $this->replaceNamedParameters($path, $parameters);

        $path = preg_replace_callback('/\{.*?\}/', function ($match) use (&$parameters) {
            return (empty($parameters) && !Str::endsWith($match[0], '?}'))
                ? $match[0]
                : array_shift($parameters);
        }, $path);

        return trim(preg_replace('/\{.*?\?\}/', '', $path), '/');
    }

    /**
     * Replace all of the named parameters in the path.
     *
     * @note: based on the Illuminate\Routing\UrlGenerator code
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceNamedParameters($path, &$parameters)
    {
        return preg_replace_callback('/\{(.*?)\??\}/', function ($m) use (&$parameters) {
            return isset($parameters[$m[1]]) ? Arr::pull($parameters, $m[1]) : $m[0];
        }, $path);
    }
}
