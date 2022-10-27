<?php

namespace markhuot\odyssey\backends;

use craft\events\IndexKeywordsEvent;
use craft\helpers\App;
use Illuminate\Support\Collection;
use markhuot\odyssey\models\Backend;
use markhuot\odyssey\Odyssey;
use markhuot\odyssey\validators\Json;
use Algolia\AlgoliaSearch\SearchClient;
use yii\db\Query;

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

    protected function getIndex()
    {
        $client = SearchClient::create(
            App::parseEnv($this->settings['applicationId']),
            App::parseEnv($this->settings['adminApiKey']),
        );

        return $client->initIndex("test_index");
    }

    /**
     * @param Collection<int, IndexKeywordsEvent> $batch
     */
    function sync(Collection $batch)
    {
        $batch = Odyssey::getInstance()->elements->hydrateElements($batch);

        $records = $batch
            ->map(function ($record) {
                $objectID = implode('-', [
                    'el'.$record['elementId'],
                    'attr'.$record['attribute'],
                    'fl'.$record['fieldId'],
                ]);
                $key = $record['attribute'] ?? \Craft::$app->fields->getFieldById($record['fieldId'])->handle;

                return [
                    'objectID' => $objectID,
                    'elementId' => (int)$record['elementId'],
                    'elementType' => get_class($record['element']),
                    'draftId' => $record['element']->draftId,
                    'revisionId' => $record['element']->revisionId,
                    'dateDeleted' => $record['element']->dateDeleted,
                    'enabled' => $record['element']->enabled,
                    'archived' => $record['element']->archived,
                    'authorId' => $record['element']->author?->id,
                    'attribute' => $record['attribute'],
                    'fieldId' => $record['fieldId'],
                    'keywords' => $record['keywords'],
                    $key => $record['keywords'],
                    'section' => [
                        'id' => $record['element']->section->id,
                        'name' => $record['element']->section->name,
                        'handle' => $record['element']->section->handle,
                    ],
                    'type' => [
                        'id' => $record['element']->type->id,
                        'name' => $record['element']->type->name,
                        'handle' => $record['element']->type->handle,
                    ]
                ];
            })
            ->toArray();

        $this->getIndex()->saveObjects($records);
    }

    function search(Query $query, ?string $keywords)
    {
        $result = $this->getIndex()->search($keywords);

        $elementIds = collect($result['hits'])
            ->pluck('elementId');

        $query->subQuery->andWhere(['elements.id' => $elementIds->toArray()]);
    }
}
