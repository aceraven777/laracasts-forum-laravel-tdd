<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Http\Controllers\Controller;

class UserAvatarController extends Controller
{
    public function store(User $user)
    {
        $this->authorize('update', $user);

        request()->validate([
            'avatar' => 'required|image'
        ]);

        $user->update([
            'avatar_path' => request()->file('avatar')->store('avatars', 'public'),
        ]);

        return response([], 204);
    }
}
