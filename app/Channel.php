<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $guarded = [];

    protected $casts = [
        'archived' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('active', function ($builder) {
            $builder->where('archived', false)
                ->orderBy('name', 'asc');
        });
    }

    /**
     * Get route key name.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get threads of channel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads()
    {
        return $this->hasMany('App\Thread');
    }

    /**
     * Setter for name attribute
     * Also set the slug of channel.
     *
     * @param string $name
     */
    public function setNameAttribute($name)
    {
        $this->attributes['name'] = $name;
        $this->attributes['slug'] = str_slug($name);
    }

    /**
     * Archive channel.
     *
     * @return Channel
     */
    public function archive()
    {
        $this->archived = true;
        $this->save();

        return $this;
    }
}
