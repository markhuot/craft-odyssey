<?php

namespace markhuot\odyssey\console;

use craft\console\Controller;
use markhuot\odyssey\Odyssey;
use yii\console\ExitCode;

class HoldingController extends Controller
{
    public $batchSize;

    function options($actionID): array
    {
        return [
            'batchSize',
        ];
    }

    function actionSync()
    {
        $params = collect($this->options('sync'))
            ->flatMap(fn ($param) => [$param => $this->$param ?? null])
            ->filter()
            ->toArray();

        call_user_func_array([Odyssey::getInstance()->holding, 'sync'], $params);

        return ExitCode::OK;
    }
}
