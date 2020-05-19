<?php

namespace nixon\instagram\migrations;

use craft\db\Migration;

/**
 * @author Nixon Design Ltd
 * @since 1.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%instagram}}', [
            'id' => $this->primaryKey(),
            'siteId' => $this->integer()->notNull(),
            'clientId' => $this->string()->notNull(),
            'clientSecret' => $this->string()->notNull(),
            'token' => $this->string()->notNull(),
            'expiryDate' => $this->dateTime()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%instagram}}');
    }
}
