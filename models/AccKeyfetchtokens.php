<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "acc_keyfetchtokens".
 *
 * @property integer $id
 * @property string $uid
 * @property string $tokenId
 * @property string $authKey
 * @property string $keyBundle
 * @property string $createdAt
 *
 * @property AccAccounts $u
 */
class AccKeyfetchtokens extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'acc_keyfetchtokens';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'tokenId', 'authKey', 'keyBundle', 'createdAt'], 'required'],
            [['uid', 'tokenId', 'authKey', 'keyBundle', 'createdAt'], 'string', 'max' => 255]
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
            'authKey' => 'Auth Key',
            'keyBundle' => 'Key Bundle',
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
