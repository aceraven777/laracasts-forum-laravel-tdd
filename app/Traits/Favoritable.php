<?php

namespace App\Traits;

use App\Favorite;

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

    /**
     * Unfavorite the reply
     * 
     * @return Model
     */
    public function unfavorite()
    {
        $favorites = $this->favorites();
        $attributes = ['user_id' => auth()->id()];
        $favorite = $favorites->where($attributes);

        return $favorites->delete();
    }

    public function isFavorited()
    {
        return !! $this->favorites
            ->where('user_id', auth()->id())
            ->count();
    }

    public function getIsFavoritedAttribute()
    {
        return $this->isFavorited();
    }

    public function getFavoritesCountAttribute()
    {
        return $this->favorites->count();
    }
}
