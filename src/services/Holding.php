<?php

namespace markhuot\odyssey\services;

use craft\db\Query;
use craft\events\IndexKeywordsEvent;
use craft\helpers\Db;
use markhuot\odyssey\db\Table;
use markhuot\odyssey\Odyssey;

class Holding
{
    function store(IndexKeywordsEvent $event)
    {
        $data = [
            'keywords' => $event->keywords,
            'dateUpdated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
        ];

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
                'dateUpdated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
            ], [
                'id' => $id,
            ])->execute();
        }
        else {
            \Craft::$app->db->createCommand()->insert(Table::HOLDING, array_merge($query, [
                'keywords' => $event->keywords,
                'dateCreated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime('now', new \DateTimeZone('UTC'))),
            ]))->execute();
        }
        
        $transaction->commit();
    }

    function sync($batchSize = 100)
    {
        $batch = (new Query)
            ->from(Table::HOLDING)
            ->whereNull('dateSynced')
            ->limit($batchSize)
            ->all();

        $backends = Odyssey::getInstance()->backends->getAllBackends();

        foreach ($backends as $backend) {
            $backend->sync($batch);
        }
    }
}