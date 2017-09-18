<?php

use yii\db\Migration;

/**
 * Init script
 *
 * @package ext-options
 * @author  Lukas Cerny <lukas.cerny@futuretek.cz>
 * @license Apache-2.0
 * @link    http://www.futuretek.cz
 */
class m150103_162805_init extends Migration
{
    public function safeUp()
    {
        $this->createTable('option', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'value' => $this->text(),
            'title' => $this->string(128),
            'description' => $this->text(),
            'data' => $this->text(),
            'default_value' => $this->text(),
            'unit' => $this->string(20),
            'system' => $this->integer(1)->notNull()->defaultValue(0),
            'type' => $this->string(1)->notNull()->defaultValue('S'),
            'context' => $this->string(16)->notNull()->defaultValue('Option'),
            'context_id' => $this->integer(11),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);
        $this->createIndex('name_context_UNIQUE', 'option', ['name', 'context', 'context_id'], true);
    }

    public function safeDown()
    {
        $this->dropTable('option');
    }
}
