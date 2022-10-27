<?php

namespace markhuot\odyssey;

use craft\base\Model;
use craft\base\Plugin;
use craft\elements\db\ElementQuery;
use craft\events\CancelableEvent;
use craft\events\DefineBehaviorsEvent;
use craft\events\IndexKeywordsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\Db;
use craft\services\Search;
use craft\web\Application;
use craft\web\UrlManager;
use markhuot\craftdata\behaviors\DataRequestBehavior;
use markhuot\odyssey\behaviors\ElementQueryBehavior;
use markhuot\odyssey\behaviors\GetFormDataBehavior;
use markhuot\odyssey\db\Table;
use markhuot\odyssey\models\Backend;
use markhuot\odyssey\models\Settings;
use markhuot\craftdata\Data;
use markhuot\odyssey\twig\Extension;
use yii\base\Event;

class Odyssey extends Plugin
{
    public bool $hasCpSettings = true;

    function init()
    {
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
                $data = [
                    'keywords' => $event->keywords,
                    'dateUpdated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                ];

                \Craft::$app->db->createCommand()->upsert(Table::HOLDING, array_merge($data, [
                    'elementId' => $event->element->id,
                    'attribute' => $event->attribute,
                    'fieldId' => $event->fieldId,
                    'dateCreated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                ], $data))->execute();
            }
        );

        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_BEFORE_PREPARE,
            function (CancelableEvent $event) {
                if (!$event->sender->getBehavior('odyssey')) {
                    return;
                }

                $event->sender->setOdysseySearch($event->sender->search);
                $event->sender->search = null;
            }
        );

        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_AFTER_PREPARE,
            function (CancelableEvent $event) {
                if (!$event->sender->getBehavior('odyssey')) {
                    return;
                }

                $search = $event->sender->getOdysseySearch();
                if (!$search) {
                    return;
                }
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
