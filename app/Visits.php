<?php

namespace App;

use Illuminate\Support\Facades\Redis;

class Visits
{
    protected $model;
    protected $prefixCacheKey;

    public function __construct($model, $prefixCacheKey)
    {
        $this->model = $model;
        $this->prefixCacheKey = $prefixCacheKey;
    }

    public function reset()
    {
        Redis::del($this->cacheKey());

        return $this;
    }

    public function count()
    {
        return Redis::get($this->cacheKey()) ?? 0;
    }

    public function record()
    {
        Redis::incr($this->cacheKey());

        return $this;
    }

    protected function cacheKey()
    {
        return (app()->environment('testing') ? 'testing_' : '') . "{$this->prefixCacheKey}.{$this->model->id}.visits";
    }
}
