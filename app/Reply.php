<?php

namespace App;

use Carbon\Carbon;
use App\Traits\Favoritable;
use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use Favoritable, RecordsActivity;

    protected $fillable = ['user_id', 'body'];

    protected $with = ['owner', 'favorites'];

    protected $appends = ['favoritesCount', 'isFavorited'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reply) {
            $reply->thread->increment('replies_count');
        });

        static::deleting(function ($reply) {
            $reply->thread->decrement('replies_count');
        });
    }   

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id');
    }

    public function path()
    {
        return $this->thread->path() . "#reply-{$this->id}";
    }

    public function wasJustPublished()
    {
        return $this->created_at->addMinute() > Carbon::now();
    }

    public function mentionedUsers($body = false)
    {
        preg_match_all('/\@([\w\-]+)/', $body ?: $this->body, $matches);

        return $matches[1];
    }

    public function setBodyAttribute($body)
    {
        $mentionedUsers = $this->mentionedUsers($body);

        $patterns = [];
        $replacements = [];
        foreach ($mentionedUsers as $user) {
            $patterns[] = '/@' . $user . '/';
            $replacements[] = '<a href="' . route('profile', [$user]) . '">@'.$user.'</a>';
        }

        $this->attributes['body'] = preg_replace($patterns, $replacements, $body);
    }
}
