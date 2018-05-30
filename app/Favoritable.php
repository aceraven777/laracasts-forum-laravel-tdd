<?php

namespace App;

trait Favoritable
{
    /**
     * A reply can be favorited
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorited');
    }

    /**
     * Favorite the current reply
     * 
     * @return Model
     */
    public function favorite()
    {
        $favorites = $this->favorites();
        $attributes = ['user_id' => auth()->id()];
        $favorite = $favorites->where($attributes);

        if (! $favorite->exists()) {
            return $favorites->create($attributes);
        }

        return $favorite->first();
    }

    public function isFavorited()
    {
        return !! $this->favorites
            ->where('user_id', auth()->id())
            ->count();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }
}
