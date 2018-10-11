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

    /**
     * Boot function
     */
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

    /**
     * Owner of the reply
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Thread of the reply
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id');
    }

    /**
     * Path of reply
     *
     * @return string
     */
    public function path()
    {
        return $this->thread->path() . "#reply-{$this->id}";
    }

    /**
     * Is reply was just published
     *
     * @return boolean
     */
    public function wasJustPublished()
    {
        return $this->created_at->addMinute() > Carbon::now();
    }

    /**
     * Get all mentioned users in the body
     *
     * @param boolean $body
     * @return array
     */
    public function mentionedUsers($body = false)
    {
        preg_match_all('/\@([\w\-]+)/', $body ?: $this->body, $matches);

        return $matches[1];
    }

    /**
     * Body attribute mutator
     *
     * @param string $body
     */
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

    /**
     * If reply is best reply
     *
     * @return boolean
     */
    public function isBest()
    {
        return $this->thread->best_reply_id == $this->id;
    }
}
