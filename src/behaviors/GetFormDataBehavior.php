<?php

namespace markhuot\odyssey\behaviors;

use craft\base\Model;
use yii\base\Behavior;

class GetFormDataBehavior extends Behavior
{
    /**
     * @return mixed
     */
    function getFormData(
        string $className,
        string $errorMessage=null,
        string $form=null,
        bool $validate=true,
    )
    {
        $data = $form ? \Craft::$app->request->getBodyParam($form) : \Craft::$app->request->getBodyParams();

        /** @var Model $model */
        $model = $className::firstOrNew($data['id'] ?? null);
        $model->setAttributes($data, false);
        if ($validate && !$model->validate()) {
            \Craft::$app->session->setError($errorMessage ?? 'Invalid data');

            foreach ($model->errors as $key => $messages) {
                \Craft::$app->session->setFlash('error.'.$key, implode(',', $messages));
            }

            $this->setOldFlashes(\Craft::$app->request->getBodyParams());

            \Craft::$app->response->redirect(\Craft::$app->request->getUrl());
            \Craft::$app->end();
        }

        return $model;
    }

    protected function setOldFlashes($array, $prefix='')
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->setOldFlashes($value, implode('.', array_filter([$prefix, $key])));
            }
            else {
                \Craft::$app->session->setFlash('old.'.implode('.', array_filter([$prefix, $key])), $value);
            }
        }
    }
}
