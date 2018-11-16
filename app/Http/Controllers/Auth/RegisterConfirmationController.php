<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RegisterConfirmationController extends Controller
{
    /**
     * Confirm user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $token = request('token');

        $user = User::where('confirmation_token', $token)
                ->first();

        if (! $user) {
            return redirect(route('threads'))
                ->with('flash', 'Unknown token.');
        }

        $user->confirm();

        Auth::login($user);

        return redirect(route('threads'))
            ->with('flash', 'Your account is now confirmed! You may post to the forum.');
    }
}
