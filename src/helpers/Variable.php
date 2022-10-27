<?php

namespace markhuot\odyssey\helpers;

class Variable
{
    static function exists($objOrArray, $key)
    {
        return static::get($objOrArray, $key, false);
    }

    static function get($objOrArray, $key, $returnValue=true)
    {
        $segments = explode('.', $key);
        $segment = array_shift($segments);

        if (is_object($objOrArray)) {
            if (!isset($objOrArray->$key) && !isset($objOrArray->$segment)) {
                return false;
            }
            $found = $objOrArray->$key ?? $objOrArray->$segment;
        }
        else if (is_array($objOrArray)) {
            if (!isset($objOrArray[$key]) && !isset($objOrArray[$segment])) {
                return false;
            }
            $found = $objOrArray[$key] ?? $objOrArray[$segment];
        }
        else {
            throw new \Exception('Could not query');
        }

        if ((is_object($found) || is_array($found)) && count($segments)) {
            return static::get($found, implode('.', $segments), $returnValue);
        }

        return $returnValue ? $found : true;
    }
}
