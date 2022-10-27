<?php

namespace markhuot\odyssey\helpers;

use craft\helpers\Db;

class Date
{
    static function now()
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }

    static function nowPreparedForDb()
    {
        return Db::prepareDateForDb(static::now());
    }
}
