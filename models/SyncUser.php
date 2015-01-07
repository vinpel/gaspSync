<?php
namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
* This is the model class for table "sync_user".
*
* @property integer $id
* @property string $user_id
* @property string $email
* @property integer $created_at
* @property integer $updated_at
*/
class SyncUser extends ActiveRecord
{
  /**
  * @inheritdoc
  */
  public static function tableName()
  {
    return 'sync_user';
  }
  /*
  */
  public function behaviors()
  {
    return [
       TimestampBehavior::className(),
    ];
  }
  /**
  * @inheritdoc
  */
  public function rules()
  {
    return [
      [['user_id', 'email'], 'required'],
      ['email', 'string', 'max' => 255],
      ['user_id', 'integer'],

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
      'email' => 'Email',

    ];
  }
}
