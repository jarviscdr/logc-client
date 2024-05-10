<?php

use Jarviscdr\LogcClient\Client;

function logc($content, $tags = [], $type = 1, $project = '') {
    if(!is_array($tags)) {
        $tags = explode(',', $tags);
    }

    Client::getInstance()->report($content, $tags, $type, $project, Client::REPORT_TYPE_API);
}