<?php

namespace app\models;

use Yii;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
* This is the generic class for all Bso tables
*
* @property integer $id
* @property integer $user_id
* @property string $bso_id
* @property string $sortindex
* @property double $modified
* @property string $payload
* @property string $payload_size
* @property string $ttl
*/
class BsoStorage extends \yii\db\ActiveRecord
{
  public function behaviors()
  {
    return [
      [
        'class' => TimestampBehavior::className(),
        'attributes' => [
          ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
          ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
        ],
      ],
    ];
  }

  /**
  * @inheritdoc
  */
  public function rules()
  {
    return [
      [['user_id', 'bso_id', 'sortindex', 'modified', 'payload', 'payload_size', 'ttl'], 'required'],
      [['user_id', 'updated_at', 'created_at'], 'integer'],
      [['sortindex', 'modified', 'payload_size'], 'number'],
      [['payload'], 'string'],
      [['ttl'], 'safe'],
      [['bso_id'], 'string', 'max' => 255]
    ];
  }

  /**
  * @inheritdoc
  */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_id' => 'User ID',
      'bso_id' => 'Bso ID',
      'sortindex' => 'Sortindex',
      'modified' => 'Modified',
      'payload' => 'Payload',
      'payload_size' => 'Payload Size',
      'ttl' => 'Ttl',
      'updated_at' => 'Updated At',
      'created_at' => 'Created At',
    ];
  }
}
