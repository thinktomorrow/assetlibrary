<?php

namespace Thinktomorrow\AssetLibrary;

use Illuminate\Support\Arr;

trait ProvidesData
{
    public function hasData(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    public function getData(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    public function setData(string $name, $value): self
    {
        $data = $this->data;

        Arr::set($data, $name, $value);

        $this->data = $data;

        return $this;
    }

    public function forgetData(string $name): self
    {
        $data = $this->data;

        Arr::forget($data, $name);

        $this->data = $data;

        return $this;
    }
}
