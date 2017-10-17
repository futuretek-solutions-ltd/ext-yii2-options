<?php

use yii\db\Migration;

/**
 * Class m171017_135314_category
 *
 * @package ext-options
 * @author  Lukas Cerny <lukas.cerny@futuretek.cz>
 * @license Apache-2.0
 * @link    http://www.futuretek.cz
 */
class m171017_135314_category extends Migration
{
    public function safeUp()
    {
        $this->addColumn('option', 'category', $this->string(64));
    }

    public function safeDown()
    {
        $this->dropColumn('option', 'category');
    }
}
