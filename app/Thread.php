<?php

namespace App;

use Laravel\Scout\Searchable;
use App\Traits\RecordsActivity;
use App\Events\ThreadWasPublished;
use App\Events\ThreadReceivedNewReply;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use RecordsActivity, Searchable;

    /**
     * Mass assignment protection.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'channel_id', 'title', 'body', 'pinned'];

    /**
     * The relationships to always eager-load.
     *
     * @var array
     */
    protected $with = ['creator', 'channel'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'locked' => 'boolean',
        'pinned' => 'boolean',
    ];

    /**
     * Boot function.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($thread) {
            $thread->slug = static::generateUniqueSlug($thread->title);

            event(new ThreadWasPublished($thread));

            $thread->creator->gainReputation('thread_published');
        });

        static::deleting(function ($thread) {
            $thread->replies->each->delete();

            $thread->creator->loseReputation('thread_published');
        });
    }

    /**
     * Generate unique slug of the thread.
     *
     * @param string $title
     * @return string
     */
    public static function generateUniqueSlug($title)
    {
        $original_slug = str_slug($title);
        $slug = $original_slug;

        $exists = static::where('slug', $slug)->exists();

        // If there are no duplicates
        if (! $exists) {
            return $slug;
        }

        // Get how many duplicates
        $max_count = static::where('title', $title)->count();

        // Check for duplicates from $max_count+1 to 2
        $i = $max_count + 1;
        do {
            $slug = $original_slug.'-'.$i;

            $exists = static::where('slug', $slug)->exists();
            $i--;
        } while ($exists && $i > 1);

        if (! $exists) {
            return $slug;
        }

        // Check for duplicates from $max_count+2 to infinity
        $i = $max_count + 2;
        do {
            $slug = $original_slug.'-'.$i;

            $exists = static::where('slug', $slug)->exists();
            $i++;
        } while ($exists);

        return $slug;
    }

    /**
     * Route key name.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Accessor for is_subscribed_to.
     *
     * @return bool
     */
    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()
            ->where('user_id', auth()->id())
            ->exists();
    }

    /**
     * Thread URI path.
     *
     * @return string
     */
    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->slug}";
    }

    /**
     * Replies to the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * A thread can have a best reply.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bestReply()
    {
        return $this->hasOne(Reply::class, 'thread_id');
    }

    /**
     * Creator of the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the title for the thread.
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * Channel of the thread it belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class)->withoutGlobalScope('active');
    }

    /**
     * Subscriptions to the thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public function addReply($reply)
    {
        $reply = $this->replies()->create($reply);

        event(new ThreadReceivedNewReply($reply));

        return $reply;
    }

    /**
     * Notify all subscribers of thread.
     *
     * @param \App\Reply $reply
     */
    public function notifySubscribers($reply)
    {
        $this->subscriptions()
            ->where('user_id', '!=', $reply->user_id)
            ->get()
            ->each
            ->notify($reply);
    }

    /**
     * Local scope filter for thread.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Filters\ThreadFilters            $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    /**
     * Subscribe a user to the current thread.
     *
     * @param int|null $userId
     * @return $this
     */
    public function subscribe($userId = null)
    {
        $this->subscriptions()->create(
            [
                'user_id' => ($userId ?: auth()->id())
            ]
        );

        return $this;
    }

    /**
     * Unsubscribe user to thread.
     *
     * @param int $userId
     */
    public function unsubscribe($userId = null)
    {
        $this->subscriptions()
            ->where('user_id', $userId ?: auth()->id())
            ->delete();
    }

    /**
     * Is user has updates in the thread.
     *
     * @param \App\User $user
     * @return bool
     */
    public function hasUpdatesFor($user)
    {
        $key = $user->visitedThreadCacheKey($this);

        return $this->updated_at > cache($key);
    }

    /**
     * Set best reply.
     *
     * @param Reply $reply
     */
    public function markBestReply(Reply $reply)
    {
        $this->best_reply_id = $reply->id;
        $this->save();

        Reputation::award($reply->owner, Reputation::BEST_REPLY_AWARDED);
    }

    /**
     * Unset best reply from thread.
     */
    public function unsetBestReply()
    {
        if ($this->hasBestReply()) {
            $this->bestReply->owner->loseReputation('best_reply_awarded');
            $this->update(['best_reply_id' => null]);
        }
    }

    /**
     * Determine if the thread has a current best reply.
     *
     * @return bool
     */
    public function hasBestReply()
    {
        return ! is_null($this->best_reply_id);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->toArray() + ['path' => $this->path()];
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
