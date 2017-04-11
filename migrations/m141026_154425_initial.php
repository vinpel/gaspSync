<?php

use yii\db\Schema;
use yii\db\Migration;

class m141026_154425_initial extends Migration
{
  private $bsoList;

  public function init(){
    $par=include(\Yii::getAlias('@app/config/params.php'));
    $this->bsoList=$par['bsoList'];
  }
  public function up()
  {
    $this->createTable('sync_collections', [
      'id' => 'pk',
      'name'=>Schema::TYPE_STRING . ' NOT NULL',
    ]
  );
  foreach ($this->bsoList as $oneBso){
    $this->db->createCommand("insert into sync_collections values (0,'".$oneBso."')")->execute();
  }
  $this->createTable('sync_user_collections', [
    'id' => 'pk',
    'user_id'=>Schema::TYPE_INTEGER . ' NOT NULL',
    'collection'=>Schema::TYPE_STRING . ' NOT NULL',
    'nb_records'=>Schema::TYPE_INTEGER,
    'lastUpdate'=>Schema::TYPE_STRING .'  NOT NULL',
    'lastAccess'=>Schema::TYPE_STRING .'  NOT NULL',
  ]
);
foreach ($this->bsoList as $oneBso){
  $this->createTAble('storage_'.$oneBso,[
    'id' => 'pk',
    'user_id'=>Schema::TYPE_INTEGER . ' NOT NULL',
    'bso_id'=>Schema::TYPE_STRING . ' NOT NULL',
    'sortindex'=>Schema::TYPE_DECIMAL. ' NOT NULL',
    'modified'=>Schema::TYPE_STRING . ' NOT NULL',
    'payload'=>Schema::TYPE_TEXT . ' NOT NULL',
    'payload_size'=>Schema::TYPE_DECIMAL . ' NOT NULL',
    'ttl'=>Schema::TYPE_INTEGER . ' NOT NULL',
    'updated_at'=>Schema::TYPE_INTEGER . ' NOT NULL',
    'created_at'=>Schema::TYPE_INTEGER . ' NOT NULL',
  ]
);
}
$this->createTable('sync_user',[
  'id'=>'pk',
  'user_id'=>Schema::TYPE_INTEGER . ' NOT NULL',
  'email'=>Schema::TYPE_STRING . ' NOT NULL',
  'created_at'=>Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
  'updated_at'=>Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
]
);
$this->createTable('sync_device',[
  'id'=>'pk',
  'user_id'=>Schema::TYPE_INTEGER . ' NOT NULL',
  'deviceid'=>Schema::TYPE_STRING . ' NOT NULL',
]
);
}
/**
*
*/
public function down()
{

  $this->dropTable('sync_device');
  $this->dropTable('sync_user');
  $this->dropTable('sync_user_collections');
  $this->dropTable('sync_collections');
  foreach ($this->bsoList as $oneBso){
    $this->dropTable('storage_'.$oneBso);
  }
}
}
