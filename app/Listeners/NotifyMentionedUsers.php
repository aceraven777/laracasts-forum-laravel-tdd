<?php

namespace App\Listeners;

use App\User;
use App\Events\ThreadReceivedNewReply;
use App\Notifications\YouWereMentioned;

class NotifyMentionedUsers
{
    /**
     * Handle the event.
     *
     * @param  ThreadReceivedNewReply  $event
     * @return void
     */
    public function handle(ThreadReceivedNewReply $event)
    {
        $reply = $event->reply;

        $mentionedUsers = $reply->mentionedUsers();
        $users = User::whereIn('name', $mentionedUsers)->get();

        foreach ($users as $user) {
            $user->notify(new YouWereMentioned($reply));
        }
    }
}
