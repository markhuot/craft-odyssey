<?php

namespace markhuot\odyssey\twig;

use markhuot\odyssey\helpers\Variable;
use yii\base\Model;
use Illuminate\Support\Arr;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('old', [$this, 'old']),
        ];
    }

    function old($key, ?Model $model=null, $default=null)
    {
        $flashes = \Craft::$app->session->getAllFlashes();
        if (Variable::exists($flashes, 'old.'.$key)) {
            return Variable::get($flashes, 'old.'.$key);
        }

        if ($model && Variable::exists($model, $key)) {
            return Variable::get($model, $key);
        }

        return $default;
    }
}
