<?php

namespace App\Http\Controllers\Admin;

use App\Channel;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class ChannelsController extends Controller
{
    public function index()
    {
        $channels = Channel::withoutGlobalScopes()->orderBy('name', 'asc')->withCount('threads')->get();

        return view('admin.channels.index', compact('channels'));
    }

    public function create()
    {
        return view('admin.channels.create', ['channel' => new Channel]);
    }

    public function store()
    {
        $data = request()->validate([
            'name' => 'required|unique:channels',
            'description' => 'required',
        ]);

        $channel = Channel::create($data);

        Cache::forget('channels');

        if (request()->wantsJson()) {
            return response($channel, 201);
        }

        return redirect(route('admin.channels.index'))
            ->with('flash', 'Your channel has been created!');
    }

    /**
     * Show the form to edit an existing channel.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Channel $channel)
    {
        return view('admin.channels.edit', compact('channel'));
    }

    /**
     * Update an existing channel.
     *
     * @return \Illuminate\
     */
    public function update(Channel $channel)
    {
        $channel->update(
            request()->validate([
                'name' => [
                    'required',
                    Rule::unique('channels')->ignore($channel->id),
                ],
                'description' => 'required',
                'archived' => 'required|boolean',
            ])
        );

        cache()->forget('channels');

        if (request()->wantsJson()) {
            return response($channel, 200);
        }

        return redirect(route('admin.channels.index'))
            ->with('flash', 'Your channel has been updated!');
    }
}
