<?php

namespace markhuot\odyssey\migrations;

use craft\db\Migration;
use markhuot\odyssey\db\Table;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable(Table::BACKENDS, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::HOLDING, [
            'id' => $this->primaryKey(),
            'elementId' => $this->integer()->unsigned(),
            'attribute' => $this->string(),
            'fieldId' => $this->integer(),
            'keywords' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateSynced' => $this->dateTime(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, Table::HOLDING, ['elementId', 'attribute', 'fieldId'], true);

        // $this->createIndex(null, Table::BACKENDS, ['uid'], true);
        // $this->addForeignKey(null, Table::BACKENDS, ['parentId'], \craft\db\Table::ELEMENTS, ['id'], 'CASCADE', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(Table::BACKENDS);
        $this->dropTableIfExists(Table::HOLDING);
        return true;
    }
}
