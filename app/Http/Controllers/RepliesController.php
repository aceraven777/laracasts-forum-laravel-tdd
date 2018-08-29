<?php

namespace App\Http\Controllers;

use App\Spam;
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
        $this->middleware('auth', ['except' => 'index']);
    }

    /**
     * Get replies of a thread.
     *
     * @param  string                   $channel
     * @param  Thread                   $thread
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function index($channel, Thread $thread, Request $request)
    {
        return $thread->replies()->paginate(20);
    }

    /**
     * Persist a new reply.
     *
     * @param  string                   $channel
     * @param  Thread                   $thread
     * @param  Spam                     $spam
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($channel, Thread $thread, Spam $spam, Request $request)
    {
        $this->validate($request, [
            'body' => 'required',
        ]);

        $spam->detect(request('body'));

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
