<?php

namespace markhuot\odyssey\models;

use markhuot\odyssey\casts\Json;
use markhuot\odyssey\db\ActiveRecord;
use markhuot\odyssey\db\Table;

class Backend extends ActiveRecord
{
    protected $casts = [
        'settings' => Json::class,
    ];

    static $polymorphicKey = 'type';

    public static function tableName()
    {
        return Table::BACKENDS;
    }

    public function rules()
    {
        return [
            [['name', 'handle', 'type'], 'required'],
        ];
    }
}
