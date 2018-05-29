<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $fillable = ['user_id', 'body'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id');
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favorited');
    }

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
        return $this->favorites()
            ->where(['user_id' => auth()->id()])
            ->exists();
    }
}
