<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\base\Event;

/*
 * This is the model class for table "sync_user_collections".
 *
 * @property integer $id
 * @property string $user_id
 * @property string $collection
 * @property integer $nb_records
 * @property string $lastUpdate
 * @property string $lastAccess
 */
class SyncUserCollections extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sync_user_collections';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'collection'], 'required'],
            [['user_id','nb_records'], 'integer'],
            [['lastUpdate', 'lastAccess'], 'safe'],
            [['collection'], 'string', 'max' => 255]
        ];
    }
    /*

    public function behaviors()
    {
      return [
        [
          'class' => TimestampBehavior::className(),
          'createdAtAttribute' => 'lastUpdate',
          'updatedAtAttribute' => 'lastUpdate',
        ],
      ];
    }
    */

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'collection' => 'Collection',
            'nb_records' => 'Nb Records',
            'lastUpdate' => 'Last Update',
            'lastAccess' => 'Last Access',
        ];
    }
}
