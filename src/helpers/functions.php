<?php

namespace markhuot\odyssey\helpers;

use yii\log\Logger;


function log($message, $level = Logger::LEVEL_INFO)
{
    \Craft::getLogger()->log($message, $level, 'odyssey');

    if (PHP_SAPI === 'cli') {
        echo str_pad(date('Y-m-d H:i:s'), 22) . $message."\n";
    }
}
