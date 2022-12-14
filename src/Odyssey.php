<?php

namespace markhuot\odyssey;

use craft\base\Model;
use craft\base\Plugin;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\events\CancelableEvent;
use craft\events\DefineBehaviorsEvent;
use craft\events\IndexKeywordsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\Db;
use craft\services\Search;
use craft\web\Application;
use craft\web\UrlManager;
use markhuot\odyssey\behaviors\ElementQueryBehavior;
use markhuot\odyssey\behaviors\GetFormDataBehavior;
use markhuot\odyssey\db\Table;
use markhuot\odyssey\models\Backend;
use markhuot\odyssey\models\Settings;
use markhuot\odyssey\services\Backends;
use markhuot\odyssey\services\Elements;
use markhuot\odyssey\services\Holding;
use markhuot\odyssey\twig\Extension;
use yii\base\Event;

/**
 * @property Holding $holding
 * @property Backends $backends
 * @property Elements $elements
 */
class Odyssey extends Plugin
{
    public bool $hasCpSettings = true;

    function init()
    {
        $this->controllerNamespace = 'markhuot\\odyssey\\controllers';
        if (\Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'markhuot\\odyssey\\console';
        }

        $this->components = [
            'holding' => Holding::class,
            'backends' => Backends::class,
            'elements' => Elements::class,
        ];

        Event::on(
            Application::class,
            Application::EVENT_BEFORE_REQUEST,
            function ($event) {
                $event->sender->getRequest()->attachBehavior('formdata', GetFormDataBehavior::class);
            }
        );

        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->behaviors['odyssey'] = ElementQueryBehavior::class;
            }
        );

        Event::on(
            Search::class,
            Search::EVENT_BEFORE_INDEX_KEYWORDS,
            function (IndexKeywordsEvent $event) {
                $this->holding->store($event);
            }
        );

        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_BEFORE_PREPARE,
            function (CancelableEvent $event) {
                $event->sender->setOdysseySearch($event->sender->search);
                $event->sender->search = null;
            }
        );

        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_AFTER_PREPARE,
            function (CancelableEvent $event) {
                $search = $event->sender->getOdysseySearch();
                if (!$search) {
                    return;
                }

                $backend = Odyssey::getInstance()->backends->getAllBackends()->first();
                $backend->search(
                    query: $event->sender,
                    keywords: $search,
                );
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['odyssey/backend/create'] = 'odyssey/backend/create';
                $event->rules['odyssey/backend/<id:.+>'] = 'odyssey/backend/create';
            }
        );

        \Craft::$app->view->registerTwigExtension(new Extension);
    }

    function createSettingsModel(): ?Model
    {
        return new Settings;
    }

    protected function settingsHtml(): ?string
    {
        return \Craft::$app->view->renderTemplate('odyssey/settings/edit.twig', [
            'settings' => $this->getSettings(),
            'backends' => Backend::find()->all(),
        ]);
    }
}
