<?php

namespace markhuot\odyssey\db;

class ActiveRecord extends \craft\db\ActiveRecord
{
    protected $casts = [];
    protected static $polymorphicKey = false;

    public static function firstOrNew($condition)
    {
        $record = static::find()->where(['id' => $condition])->asArray()->one();

        return static::make($record);
    }
    
    static function make(array $record)
    {
        $type = static::$polymorphicKey ? ($record[static::$polymorphicKey] ?? static::class) : static::class;
    
        $model = new $type;
        $model->setAttributes($record, false);
        $model->setIsNewRecord(false);
        return $model;
    }

    function __get($key)
    {
        if ($caster = ($this->casts[$key] ?? false)) {
            return (new $caster)->get($this, $key, $this->getAttribute($key));
        }

        return parent::__get($key);
    }
}
