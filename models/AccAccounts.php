<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "acc_accounts".
 *
 * @property integer $id
 * @property string $uid
 * @property string $normalizedEmail
 * @property string $email
 * @property string $emailCode
 * @property integer $emailVerified
 * @property string $kA
 * @property string $wrapWrapKb
 * @property string $authSalt
 * @property string $verifyHash
 * @property integer $verifierVersion
 * @property integer $verifierSetAt
 *
 * @property AccKeyfetchtokens[] $accKeyfetchtokens
 * @property AccSessionTokens[] $accSessionTokens
 */
class AccAccounts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'acc_accounts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'normalizedEmail', 'email', 'kA', 'wrapWrapKb', 'authSalt', 'verifyHash', 'verifierSetAt'], 'required'],
            [['verifierVersion', 'verifierSetAt'], 'integer'],
            ['emailVerified','boolean'],
            [['uid', 'normalizedEmail', 'email', 'emailCode', 'kA', 'wrapWrapKb', 'authSalt', 'verifyHash'], 'string', 'max' => 255]
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
            'normalizedEmail' => 'Normalized Email',
            'email' => 'Email',
            'emailCode' => 'Email Code',
            'emailVerified' => 'Email Verified',
            'kA' => 'K A',
            'wrapWrapKb' => 'Wrap Wrap Kb',
            'authSalt' => 'Auth Salt',
            'verifyHash' => 'Verify Hash',
            'verifierVersion' => 'Verifier Version',
            'verifierSetAt' => 'Verifier Set At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccKeyfetchtokens()
    {
        return $this->hasMany(AccKeyfetchtokens::className(), ['uid' => 'uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccSessionTokens()
    {
        return $this->hasMany(AccSessionTokens::className(), ['uid' => 'uid']);
    }
}
