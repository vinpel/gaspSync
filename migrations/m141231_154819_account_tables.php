<?php

use yii\db\Schema;
use yii\db\Migration;

class m141231_154819_account_tables extends Migration
{
    public function up()
    {
      $this->createTable('acc_accounts', [
        'id' => 'pk',
        'uid'     =>Schema::TYPE_STRING . ' NOT NULL',
        'normalizedEmail'=>Schema::TYPE_STRING . ' NOT NULL',
        'email'=>Schema::TYPE_STRING.'  NOT NULL',
        'emailCode'=>Schema::TYPE_STRING,
        'emailVerified'=>Schema::TYPE_BOOLEAN .'  DEFAULT FALSE',
        'kA'=>Schema::TYPE_STRING .'  NOT NULL',
        'wrapWrapKb'=>Schema::TYPE_STRING .'  NOT NULL',
        'authSalt'=>Schema::TYPE_STRING .'  NOT NULL',
        'verifyHash'=>Schema::TYPE_STRING .'  NOT NULL',
        'verifierVersion'=>Schema::TYPE_BIGINT,
        'verifierSetAt'=>Schema::TYPE_BIGINT .'  NOT NULL',
        'INDEX `INDX_acc_accounts` (`uid`)',

        ]
      );
      $this->createTable('acc_keyfetchtokens', [
        'id' => 'pk',
        'uid'     =>Schema::TYPE_STRING . ' NOT NULL',
        'tokenId'=>Schema::TYPE_STRING . ' NOT NULL',
        'authKey'=>Schema::TYPE_STRING . ' NOT NULL',
        'uid'=>Schema::TYPE_STRING .'  NOT NULL',
        'keyBundle'=>Schema::TYPE_STRING .'  NOT NULL',
        'createdAt'=>Schema::TYPE_STRING .'  NOT NULL',
        'INDEX `FK_acc_keyfetchtokens_acc_accounts` (`uid`),
        CONSTRAINT `FK_acc_keyfetchtokens_acc_accounts` FOREIGN KEY (`uid`) REFERENCES `acc_accounts` (`uid`)'
        ]
      );
      $this->createTable('acc_sessionTokens', [
        'id' => 'pk',
        'uid'     =>Schema::TYPE_STRING . ' NOT NULL',
        'tokenId'=>Schema::TYPE_STRING . ' NOT NULL',
        'tokenData'=>Schema::TYPE_STRING . ' NOT NULL',
        'createdAt'=>Schema::TYPE_STRING .'  NOT NULL',
        'INDEX `FK_acc_sessionTokens_acc_accounts` (`uid`),
        CONSTRAINT `FK_acc_sessionTokens_acc_accounts` FOREIGN KEY (`uid`) REFERENCES `acc_accounts` (`uid`)'
        ]
      );


    }

    public function down()
    {

        $this->dropTable('acc_keyfetchtokens');
        $this->dropTable('acc_sessionTokens');
        $this->dropTable('acc_accounts');


    }
}
