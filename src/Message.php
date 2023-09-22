<?php

namespace langdonglei;

use JPush\Client;

class Message
{
    public static function aurora($rid, $alert, $title = ''): array
    {
        return (new Client(Str::env('jp.key'), Str::env('jp.secret'), null))->push()
            ->setPlatform('all')
            ->addRegistrationId($rid)
            ->androidNotification($alert, [
                'title'         => $title,
                'badge_add_num' => 1
            ])->send();
    }
}