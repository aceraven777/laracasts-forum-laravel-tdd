<?php

namespace App\Http\Controllers;

use App\Reply;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    /**
     * Create a new RepliesController instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Reply $reply)
    {
        $reply->favorite();

        if (request()->expectsJson()) {
            return response(['status' => 'Reply has been favorited']);
        }

        return back();
    }

    public function destroy(Reply $reply)
    {
        $reply->unfavorite();

        if (request()->expectsJson()) {
            return response(['status' => 'Reply has been favorited']);
        }

        return back();
    }
}
