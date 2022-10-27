<?php

namespace markhuot\odyssey\services;

use markhuot\odyssey\models\Backend;
use Illuminate\Support\Collection;

class Backends
{
    /** @var Collection<int, Backend>|null */
    protected $backends = null;

    /**
     * @return Collection<int, Backend>
     */
    function getAllBackends()
    {
        if ($this->backends !== null) {
            return $this->backends;
        }

        $records = Backend::find()->asArray()->all();

        return $this->backends = collect(array_map(function($record) {
            return Backend::make($record);
        }, $records));
    }
}
