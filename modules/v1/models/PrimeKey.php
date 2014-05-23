<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-5-22
 * Time: 下午5:53
 */

namespace app\modules\v1\models;

use app\common;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;


class PrimeKey extends common\models\PrimeKey implements Linkable
{
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['primekey', 'id' => $this->id], true),
        ];
    }
} 