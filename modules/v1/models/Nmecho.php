<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-5-22
 * Time: 下午5:19
 */

namespace app\modules\v1\models;

use app;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;


class Nmecho extends app\common\models\Nmecho implements Linkable
{
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['nmecho', 'id' => $this->id], true),
        ];
    }

    // filter out some fields, best used when you want to inherit the parent implementation
    // and blacklist some sensitive fields.
    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
//        unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

        return $fields;
    }
/*
 * 另外一种方式
    // explicitly list every field, best used when you want to make sure the changes
    // in your DB table or model attributes do not cause your field changes (to keep API backward compatibility).
    public function fields()
    {
        return [
            // field name is the same as the attribute name
            'id',
            // field name is "email", the corresponding attribute name is "email_address"
            'email' => 'email_address',
            // field name is "name", its value is defined by a PHP callback
            'name' => function () {
                    return $this->first_name . ' ' . $this->last_name;
                },
        ];
    }
*/
} 