<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "storage_meta".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $bso_id
 * @property string $sortindex
 * @property double $modified
 * @property string $payload
 * @property string $payload_size
 * @property string $ttl
 * @property integer $updated_at
 * @property integer $created_at
 */
class StorageMeta extends BsoStorage
{
  /**
  * @inheritdoc
  */
  public static function tableName()
  {
    return '{{%storage_meta}}';
  }
}
