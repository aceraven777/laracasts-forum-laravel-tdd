<?php

namespace App\Http\Controllers;

use App\User;

class UserNotificationsController extends Controller
{
    /**
     * Create a new UserNotificationsController instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get user unread notifications.
     *
     * @param  User   $user
     * @return string
     */
    public function index(User $user)
    {
        return auth()->user()->unreadNotifications;
    }

    /**
     * Clear user notifications.
     *
     * @param  User $user
     * @param  int  $notification_id
     */
    public function destroy(User $user, $notification_id)
    {
        auth()->user()->notifications()->findOrFail($notification_id)->markAsRead();
    }
}
