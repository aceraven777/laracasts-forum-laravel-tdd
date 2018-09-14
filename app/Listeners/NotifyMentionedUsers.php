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

        foreach ($mentionedUsers as $name) {
            $user = User::whereName($name)->first();

            if ($user) {
                $user->notify(new YouWereMentioned($reply));
            }
        }
    }
}
