<?php

namespace app\common\models;

use Yii;

/**
 * This is the model class for table "nm_primekey".
 *
 * @property integer $id
 * @property string $table_name
 * @property integer $max
 */
class PrimeKey extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'nm_primekey';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['table_name', 'max'], 'required'],
            [['max'], 'integer'],
            [['table_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'table_name' => 'Table Name',
            'max' => 'Max',
        ];
    }

    public static function maxPrimeKey($tableName)
    {
        $result = self::findOne(['table_name' => $tableName]);

        if ($result != NULL) {
            return $result->max;
        }

        return 0;
    }
}
