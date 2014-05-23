<?php

namespace app\common\models;

use Yii;

/**
 * This is the model class for table "nm_echo".
 *
 * @property string $id
 * @property string $content
 * @property string $user
 * @property string $create_time
 */
class Nmecho extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'nm_echo';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'content', 'user'], 'required'],
            [['create_time'], 'safe'],
            [['id', 'user'], 'string', 'max' => 50],
            [['content'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
            'user' => 'User',
            'create_time' => 'Create Time',
        ];
    }

    public static function maxPrimeKey()
    {
        $tableName = Nmecho::tableName();
        $result = PrimeKey::findOne(['table_name' => $tableName]);

        return $result->max;
    }
}
