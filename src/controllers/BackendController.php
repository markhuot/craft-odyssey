<?php

namespace markhuot\odyssey\controllers;

use craft\helpers\UrlHelper;
use craft\web\Controller;
use markhuot\odyssey\models\Backend;

class BackendController extends Controller
{
    function actionCreate(int $id=null)
    {
        $backend = Backend::firstOrNew($id);

        return $this->asCpScreen()
            ->title(($backend->isNewRecord ? 'Add' : 'Edit') . ' backend')
            ->addCrumb('Settings', UrlHelper::cpUrl('settings'))
            ->addCrumb('Plugins', UrlHelper::cpUrl('settings/plugins'))
            ->addCrumb('Odyssey', UrlHelper::cpUrl('settings/plugins/odyssey'))
            ->action('odyssey/backend/store')
            ->addAltAction('Delete backend', [
                'destructive' => true,
                'action' => 'odyssey/backend/delete',
                'redirect' => 'settings/plugins/odyssey',
                'comfirm' => 'Are you sure you want to delete this backend?',
                'params' => ['id' => $backend->id]
            ])
            ->redirectUrl(UrlHelper::cpUrl('settings/plugins/odyssey'))
            ->contentTemplate('odyssey/_backends/create', [
                'backend' => $backend,
            ]);
    }

    function actionStore()
    {
        $this->requirePostRequest();
        $backend = $this->request->getFormData(Backend::class);
        $backend->save();

        return $this->redirect('settings/plugins/odyssey');
    }

    function actionDelete()
    {
        $this->requirePostRequest();
        $backend = $this->request->getFormData(Backend::class, validate: false);
        $backend->delete();

        return $this->redirect('settings/plugins/odyssey');
    }
}
