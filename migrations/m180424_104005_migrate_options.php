<?php

class m180424_104005_migrate_options extends \futuretek\migrations\FtsMigration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $isUpgrade = $this->db->schema->getTableSchema('option') !== null;

        $tableName = $isUpgrade ? '{{%option_mig}}' : '{{%option}}';

        $this->createTable($tableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'value' => $this->text(),
            'context' => $this->string(128),
            'context_id' => $this->string(64),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        if ($isUpgrade) {
            $query = (new \yii\db\Query())
                ->select(['name', 'value', 'context', 'context_id', 'created_at', 'updated_at'])
                ->from('option');

            foreach ($query->all() as $item) {
                $this->insert($tableName, $item);
            }
            $this->update($tableName, ['context' => null], ['context' => 'Option']);

            $this->dropTable('option');
            $this->renameTable($tableName, 'option');
        }

        $this->createIndex('option_unique', 'option', ['name', 'context', 'context_id'], true);
        $this->createIndex('option_name', 'option', 'name');
    }

    /**
     * @inheritdoc
     * @throws \Exception
     * @throws \yii\base\NotSupportedException
     */
    public function safeDown()
    {
        throw new \yii\base\NotSupportedException('Migrate down is not supported.');
    }
}
