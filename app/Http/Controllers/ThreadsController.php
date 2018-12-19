<?php

namespace App\Http\Controllers;

use App\Thread;
use App\Channel;
use App\Trending;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use App\Filters\ThreadFilters;
use Illuminate\Validation\Rule;

class ThreadsController extends Controller
{
    /**
     * Create a new ThreadsController instance.
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request   $request
     * @param  \App\Channel               $channel
     * @param  \App\Filters\ThreadFilters $filters
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Channel $channel, ThreadFilters $filters)
    {
        $threads = $this->getThreads($channel, $filters);

        if ($request->wantsJson()) {
            return $threads;
        }

        return view('threads.index', [
            'threads' => $threads,
            'channel' => $channel,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('threads.create', [
            'channels' => Channel::where('archived', false)->orderBy('name', 'asc')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Rules\Recaptcha      $recaptcha
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Recaptcha $recaptcha)
    {
        $request->validate([
            'title' => 'required|spamfree',
            'body' => 'required|spamfree',
            'channel_id' => [
                'required',
                Rule::exists('channels', 'id')->where(function ($query) {
                    $query->where('archived', false);
                }),
            ],
            'g-recaptcha-response' => ['required', $recaptcha],
        ]);

        $thread = Thread::create([
            'user_id' => auth()->id(),
            'channel_id' => request('channel_id'),
            'title' => request('title'),
            'body' => request('body'),
        ]);

        if ($request->wantsJson()) {
            return response($thread, 201);
        }

        return redirect($thread->path())
            ->with('flash', 'Your thread has been published!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Trending $trending
     * @param  string        $channel
     * @param  \App\Thread   $thread
     * @return \Illuminate\Http\Response
     */
    public function show(Trending $trending, $channel, Thread $thread)
    {
        if (auth()->check()) {
            auth()->user()->read($thread);
        }

        $trending->push($thread);

        $thread->increment('visits');

        return view('threads.show', compact('thread'));
    }

    /**
     * Update the thread.
     *
     * @param Request $request
     * @param string  $channel
     * @param Thread  $thread
     */
    public function update(Request $request, $channel, Thread $thread)
    {
        $this->authorize('update', $thread);

        $data = $request->validate([
            'title' => 'required|spamfree',
            'body' => 'required|spamfree',
        ]);

        $thread->update($data);

        return $thread;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string        $channel
     * @param  \App\Thread   $thread
     * @return \Illuminate\Http\Response
     */
    public function destroy($channel, Thread $thread)
    {
        $this->authorize('update', $thread);

        $thread->delete();

        if (request()->wantsJson()) {
            return response([], 204);
        }

        return redirect('/threads');
    }

    /**
     * Get threads.
     *
     * @param  \App\Channel               $channel
     * @param  \App\Filters\ThreadFilters $filters
     * @return mixed
     */
    protected function getThreads($channel, $filters)
    {
        $threads = Thread::orderBy('pinned', 'DESC')
            ->latest()
            ->filter($filters);

        if ($channel->exists) {
            $threads->where('channel_id', $channel->id);
        }

        return $threads->paginate(25);
    }
}
