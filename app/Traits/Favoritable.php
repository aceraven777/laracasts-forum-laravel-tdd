<?php

namespace App\Traits;

use App\Favorite;
use App\Reputation;

trait Favoritable
{
    protected static function bootFavoritable()
    {
        static::deleting(function ($model) {
            $model->favorites->each->delete();
        });
    }

    /**
     * A reply can be favorited.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorited');
    }

    /**
     * Favorite the current reply.
     *
     * @return Model
     */
    public function favorite()
    {
        $favorites = $this->favorites();
        $attributes = ['user_id' => auth()->id()];
        $favorite = $favorites->where($attributes);

        if (! $favorite->exists()) {
            Reputation::award(auth()->user(), Reputation::REPLY_FAVORITED);

            return $favorites->create($attributes);
        }

        return $favorite->first();
    }

    /**
     * Unfavorite the reply.
     *
     * @return Model
     */
    public function unfavorite()
    {
        $favorites = $this->favorites();
        $attributes = ['user_id' => auth()->id()];
        $favorites->where($attributes);

        if ($favorites->exists()) {
            Reputation::reduce(auth()->user(), Reputation::REPLY_FAVORITED);
        }

        return $favorites->get()->each->delete();
    }

    public function isFavorited()
    {
        return (bool) $this->favorites
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
