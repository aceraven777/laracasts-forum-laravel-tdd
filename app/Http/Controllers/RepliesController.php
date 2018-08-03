<?php

namespace App\Http\Controllers;

use App\Reply;
use App\Thread;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    /**
     * Create a new RepliesController instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Persist a new reply.
     *
     * @param  string                   $channel
     * @param  Thread                   $thread
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($channel, Thread $thread, Request $request)
    {
        $this->validate($request, [
            'body' => 'required',
        ]);

        $reply = $thread->addReply([
            'user_id' => auth()->id(),
            'body' => request('body'),
        ]);

        if ($request->wantsJson()) {
            return $reply->load('owner');
        }

        return back()->with('flash', 'Your reply has been left.');
    }

    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);
        
        $reply->update(request(['body']));
    }

    public function destroy(Reply $reply)
    {
        $this->authorize('update', $reply);

        $reply->delete();

        if (request()->expectsJson()) {
            return response(['status' => 'Reply deleted']);
        }

        return back();
    }
}
