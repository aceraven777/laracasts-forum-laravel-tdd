<?php

namespace App\Http\Controllers;

use App\Inspections\Spam;
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
        $this->validateReply();

        $reply = $thread->addReply([
            'user_id' => auth()->id(),
            'body' => request('body'),
        ]);

        if ($request->wantsJson()) {
            return $reply->load('owner');
        }

        return back()->with('flash', 'Your reply has been left.');
    }

    /**
     * Update an existing reply.
     *
     * @param Reply $reply
     */
    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

        $this->validateReply();
        
        $reply->update(request(['body']));
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

    protected function validateReply()
    {
        $this->validate(request(), [
            'body' => 'required',
        ]);

        resolve(Spam::class)->detect(request('body'));
    }
}
