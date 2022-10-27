<?php

namespace markhuot\odyssey\services;

use craft\db\Query;
use craft\events\IndexKeywordsEvent;
use markhuot\odyssey\db\Table;
use markhuot\odyssey\helpers\Date;
use markhuot\odyssey\Odyssey;
use yii\log\Logger;
use function markhuot\odyssey\helpers\log;

class Holding
{
    function store(IndexKeywordsEvent $event)
    {
        $transaction = \Craft::$app->db->beginTransaction();
        
        $query = [
            'elementId' => $event->element->id,
            'attribute' => $event->attribute,
            'fieldId' => $event->fieldId,
            'dateSynced' => null,
        ];

        $id = (new Query)
            ->select('id')
            ->from(Table::HOLDING)
            ->where($query)
            ->scalar();

        if ($id) {
            \Craft::$app->db->createCommand()->update(Table::HOLDING, [
                'keywords' => $event->keywords,
                'dateUpdated' => Date::nowPreparedForDb(),
            ], [
                'id' => $id,
            ])->execute();
        }
        else {
            \Craft::$app->db->createCommand()->insert(Table::HOLDING, array_merge($query, [
                'keywords' => $event->keywords,
                'dateCreated' => Date::nowPreparedForDb(),
                'dateUpdated' => Date::nowPreparedForDb(),
            ]))->execute();
        }
        
        $transaction->commit();
    }

    function sync($batchSize = 100)
    {
        log('Starting batch sync');

        $batch = (new Query)
            ->from(Table::HOLDING)
            ->where(['dateSynced' => null])
            ->limit($batchSize)
            ->collect();

        if ($batch->isEmpty()) {
            log('Batch was empty, bailing');
            return;
        }

        log("Limited to {$batchSize}, found {$batch->count()} records");

        $backends = Odyssey::getInstance()->backends->getAllBackends();
        log('Syncing to '.count($backends).' backends, '.$backends->pluck('name')->join(', '));

        $backends->each->sync($batch);

        log('Sync complete across all backends');
        log('Updating local state');

        $ids = $batch->pluck('id');
        $rows = \Craft::$app->db->createCommand()->update(Table::HOLDING, [
            'dateSynced' => Date::nowPreparedForDb(),
        ], [
            'id' => $ids,
        ])->execute();

        log('Local state updated '.$rows.' rows');
        log('Sync batch complete');
    }
}
