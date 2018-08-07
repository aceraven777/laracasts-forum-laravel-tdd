<?php

namespace App\Filters;

use App\User;

class ThreadFilters extends Filters
{
    protected $filters = ['by', 'popular', 'unanswered'];

    /**
     * Filter the query by a given username.
     *
     * @param  string $username
     * @return mixed
     */
    protected function by($username)
    {
        if ($username) {
            $user = User::where('name', $username)->firstOrFail();
            $this->builder->where('user_id', $user->id);
        }

        return $this->builder;
    }

    /**
     * Filter the query according to most popular threads
     *
     * @param  string $is_popular
     * @return mixed
     */
    protected function popular($is_popular)
    {
        return $this->builder->orderBy('replies_count', ($is_popular ? 'DESC' : 'ASC'));
    }

    /**
     * Filter the query with threads with no replies
     *
     * @param  string $is_unanswered
     * @return mixed
     */
    protected function unanswered($is_unanswered)
    {
        if ($is_unanswered) {
            return $this->builder->where('replies_count', 0);
        }

        return $this->builder;
    }
}