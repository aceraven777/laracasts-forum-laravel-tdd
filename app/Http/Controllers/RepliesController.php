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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($channel, Thread $thread, Request $request)
    {
        try {
            $this->validate(request(), [
                'body' => 'required|spamfree',
            ]);

            $reply = $thread->addReply([
                'user_id' => auth()->id(),
                'body' => request('body'),
            ]);
        } catch (\Exception $e) {
            return response('Sorry, your reply could not be saved at this time.', 422);
        }

        return $reply->load('owner');
    }

    /**
     * Update an existing reply.
     *
     * @param Reply $reply
     */
    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

        try {
            $this->validate(request(), [
                'body' => 'required|spamfree',
            ]);
            
            $reply->update(request(['body']));
        } catch (\Exception $e) {
            return response('Sorry, your reply could not be saved at this time.', 422);
        }
    }

    /**
     * Delete the given reply.
     *
     * @param Reply $reply
     */
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
