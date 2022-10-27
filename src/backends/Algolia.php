<?php

namespace markhuot\odyssey\backends;

use markhuot\odyssey\models\Backend;
use markhuot\odyssey\validators\Json;

class Algolia extends Backend
{
    function rules(): array
    {
        return array_merge(parent::rules(), [
            [['settings'], Json::class, 'rules' => [
                [['applicationId', 'searchApiKey', 'adminApiKey'], 'required']]
            ],
        ]);
    }

    function settingsHtml()
    {
        return \Craft::$app->view->renderTemplate('odyssey/_backends/_algolia.twig', [
            'backend' => $this,
        ]);
    }

    function sync()
    {

    }
}
