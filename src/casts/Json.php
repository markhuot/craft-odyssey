<?php

namespace markhuot\odyssey\casts;

class Json
{
    function get($model, $key, $value)
    {
        return json_decode($value, true);
    }

    function set($model, $key, $value)
    {
        return json_encode($value);
    }
}
