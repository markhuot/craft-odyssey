<?php

namespace markhuot\odyssey\services;

use markhuot\odyssey\models\Backend;

class Backends
{
    /** @var null|Backend[] */
    protected ?array $backends = null;

    function getAllBackends()
    {
        if ($this->backends !== null) {
            return $this->backends;
        }

        $records = Backend::find()->asArray()->all();

        return $this->backends = array_map(function($record) {
            return Backend::make($record);
        }, $records);
    }
}
