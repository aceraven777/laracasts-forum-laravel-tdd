<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar_path', 'confirmation_token',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
      'isAdmin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email',
    ];

    protected $casts = [
        'confirmed' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Threads posted by user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads()
    {
        return $this->hasMany(Thread::class)->latest();
    }

    /**
     * User activities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(Activity::class)->latest();
    }

    /**
     * User last reply.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lastReply()
    {
        return $this->hasOne(Reply::class)->latest();
    }

    /**
     * Get cache key for the thread.
     *
     * @param Thread $thread
     */
    public function visitedThreadCacheKey($thread)
    {
        return sprintf('users.%s.visits.%s', $this->id, $thread->id);
    }

    /**
     * Mark thread as read.
     *
     * @param Thread $thread
     */
    public function read($thread)
    {
        $key = $this->visitedThreadCacheKey($thread);
        cache()->forever($key, Carbon::now());
    }

    /**
     * Confirm the user.
     */
    public function confirm()
    {
        $this->confirmed = true;
        $this->confirmation_token = null;
        $this->save();
    }

    /**
     * Determine if the user is an administrator.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return in_array($this->email, config('council.administrators'));
    }

    /**
     * Determine if the user is an administrator.
     *
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return $this->isAdmin();
    }

    /**
     * Accessor for avatar_path.
     *
     * @param [type] $avatar
     * @return void
     */
    public function getAvatarPathAttribute($avatar)
    {
        return $avatar ? asset('storage/'.$avatar) : asset('images/avatars/default.svg');
    }

    /**
     * Get confirmation token of user.
     *
     * @param string $email
     * @return string
     */
    public static function generateConfirmationToken($email)
    {
        do {
            $token = str_limit(md5($email.str_random()), 25, '');

            $exists = self::where('confirmation_token', $token)->exists();
        } while ($exists);

        return $token;
    }
}
