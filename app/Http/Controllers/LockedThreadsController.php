<?php

namespace App\Http\Controllers;

use App\Thread;

class LockedThreadsController extends Controller
{
    /**
     * Lock the thread
     *
     * @param Thread $thread
     * @return Response
     */
    public function store(Thread $thread)
    {
        $thread->locked = true;
        $thread->save();

        return response('Successful', 200);
    }

    /**
     * Unlock the thread
     *
     * @param Thread $thread
     * @return Response
     */
    public function destroy(Thread $thread)
    {
        $thread->locked = false;
        $thread->save();

        return response('Successful', 200);
    }
}
