<?php

use Jarviscdr\LogcClient\Client;
use Jarviscdr\LogcClient\Constant;

function logc($content, $tags = [], $type = Constant::ERROR, $project = '') {
    if(!is_array($tags)) {
        $tags = explode(',', $tags);
    }

    Client::getInstance()->report($content, $tags, $type, $project, Constant::REPORT_TYPE_API);
}