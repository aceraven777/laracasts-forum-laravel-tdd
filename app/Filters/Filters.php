<?php

namespace App\Filters;

use Illuminate\Http\Request;

abstract class Filters
{
    /*
     * @var \Illuminate\Http\Request
     */
    protected $request;

    protected $builder;

    protected $filters = [];

    /**
     * Create ThreadFilters instance.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply($builder)
    {
        $this->builder = $builder;

        foreach ($this->getFilters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                return $this->$filter($value);
            }
        }

        return $this->builder;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array_filter($this->request->only($this->filters));
    }
}