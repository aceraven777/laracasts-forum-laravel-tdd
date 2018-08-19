<?php

namespace App;

use App\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use RecordsActivity;

    protected $fillable = ['user_id', 'channel_id', 'title', 'body'];

    protected $with = ['creator', 'channel'];
    
    protected $appends = ['isSubscribedTo'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($thread) {
            $thread->replies->each->delete();
        });
    }    

    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()
            ->where('user_id', auth()->id())
            ->exists();
    }

    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->id}";
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public function addReply($reply)
    {
        $reply = $this->replies()->create($reply);

        $this->subscriptions
            ->filter(function ($subscription) use ($reply) {
                return $subscription->user_id != $reply->user_id;
            })
            ->each->notify($reply);

        return $reply;
    }

    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    /**
     * Subscribe a user to the current thread
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

    public function unsubscribe($userId = null)
    {
        $this->subscriptions()
            ->where('user_id', $userId ?: auth()->id())
            ->delete();
    }
}
