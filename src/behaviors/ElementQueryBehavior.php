<?php

namespace markhuot\odyssey\behaviors;

use yii\base\Behavior;

class ElementQueryBehavior extends Behavior
{
    protected ?string $search;

    function setOdysseySearch(?string $search)
    {
        $this->search = $search;
    }

    function getOdysseySearch()
    {
        return $this->search;
    }
}
