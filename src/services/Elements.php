<?php

namespace markhuot\odyssey\services;

use craft\db\Query;
use craft\db\Table;
use craft\elements\Entry;
use Illuminate\Support\Collection;

class Elements
{
    function hydrateElements(Collection $collection, string $key='elementId')
    {
        $mapping = (new Query)
            ->select(['id', 'type'])
            ->from(Table::ELEMENTS)
            ->where(['id' => $collection->pluck($key)->toArray()])
            ->collect();

        $hydrated = $mapping
            ->groupBy('type')
            ->map(function ($ids, $type) {
                /** @var Entry $type */
                return $type::find()
                    ->status(null)
                    ->id($ids->pluck('id')->unique()->toArray())
                    ->collect();
            })
            ->flatten();

        $collection = $collection->map(function ($item) use ($hydrated) {
            $item['element'] = $hydrated->where('id', '=', $item['elementId'])->first();
            return $item;
        });

        $collection = $collection->filter(function ($item) {
            return !empty($item['element']);
        });

        return $collection;
    }
}
