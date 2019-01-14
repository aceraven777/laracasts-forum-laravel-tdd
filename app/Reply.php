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

    protected $appends = ['favoritesCount', 'isFavorited', 'isBest', 'xp'];

    /**
     * Boot function.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reply) {
            $reply->thread->increment('replies_count');

            Reputation::award($reply->owner, Reputation::REPLY_POSTED);
        });

        static::deleting(function ($reply) {
            $reply->thread->decrement('replies_count');

            Reputation::reduce($reply->owner, Reputation::REPLY_POSTED);

            if ($reply->isBest()) {
                $reply->thread->unsetBestReply();
            }
        });
    }

    /**
     * Owner of the reply.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Thread of the reply.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id');
    }

    /**
     * Get the related title for the reply.
     */
    public function title()
    {
        return $this->thread->title;
    }

    /**
     * Path of reply.
     *
     * @return string
     */
    public function path()
    {
        return $this->thread->path()."#reply-{$this->id}";
    }

    /**
     * Is reply was just published.
     *
     * @return bool
     */
    public function wasJustPublished()
    {
        return $this->created_at->addMinute() > Carbon::now();
    }

    /**
     * Get all mentioned users in the body.
     *
     * @param bool $body
     * @return array
     */
    public function mentionedUsers($body = false)
    {
        preg_match_all('/\@([\w\-]+)/', $body ?: $this->body, $matches);

        return $matches[1];
    }

    /**
     * If reply is best reply.
     *
     * @return bool
     */
    public function isBest()
    {
        return $this->thread->best_reply_id == $this->id;
    }

    /**
     * If reply is the best reply in thread.
     *
     * @return bool
     */
    public function getIsBestAttribute()
    {
        return $this->isBest();
    }

    public function getXpAttribute()
    {
        $xp = $this->isBest() ? config('council.reputation.best_reply_awarded') : 0;
        $xp += config('council.reputation.reply_posted');
        $xp += $this->favorites()->count() * config('council.reputation.reply_favorited');

        return $xp;
    }

    /**
     * Sanitize body attribute.
     *
     * @param string $body
     * @return string
     */
    public function getBodyAttribute($body)
    {
        return \Purify::clean($body);
    }
}
