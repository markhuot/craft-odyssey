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
        $elementTypeMapping = (new Query)
            ->select(['id', 'type'])
            ->from(Table::ELEMENTS)
            ->where(['id' => $collection->pluck($key)->toArray()])
            ->collect();

        $hydrated = $elementTypeMapping
            ->groupBy('type')
            ->map(function ($ids, $type) {
                return $type::find()
                    ->status(null)
                    ->id($ids->pluck('id')->unique()->toArray())
                    ->collect();
            })
            ->flatten();

        $collection = $collection->map(function ($item) use ($key, $hydrated) {
            $item['element'] = $hydrated->where('id', '=', $item[$key])->first();
            
            return $item;
        });

        $collection = $collection->filter(function ($item) {
            return !empty($item['element']);
        });

        return $collection;
    }
}
