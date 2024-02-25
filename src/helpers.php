<?php

use Jarviscdr\LogcClient\Client;

function logc($content, $type = 1, $tags = []) {
    if(!is_array($tags)) {
        $tags = explode(',', $tags);
    }

    Client::getInstance()->report($content, $tags, $type);
}