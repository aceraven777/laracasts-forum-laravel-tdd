<?php

namespace App\Http\Controllers;

use App\Thread;

class LockedThreadsController extends Controller
{
    public function store(Thread $thread)
    {
        $thread->lock();

        return response('Successful', 200);
    }
}
