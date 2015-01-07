<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "acc_sessionTokens".
 *
 * @property integer $id
 * @property string $uid
 * @property string $tokenId
 * @property string $tokenData
 * @property string $createdAt
 *
 * @property AccAccounts $u
 */
class AccSessionTokens extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'acc_sessionTokens';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'tokenId', 'tokenData', 'createdAt'], 'required'],
            [['uid', 'tokenId', 'tokenData', 'createdAt'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'tokenId' => 'Token ID',
            'tokenData' => 'Token Data',
            'createdAt' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(AccAccounts::className(), ['uid' => 'uid']);
    }
}
