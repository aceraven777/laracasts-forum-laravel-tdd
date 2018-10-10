<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class RegisterConfirmationController extends Controller
{
    /**
     * Confirm user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $token = request('token');

        $user = User::whereConfirmationToken($token)->firstOrFail();
        
        $user->confirm();

        Auth::login($user);

        return redirect('/threads')
            ->with('flash', 'Your account is now confirmed! You may post to the forum.');
    }
}
