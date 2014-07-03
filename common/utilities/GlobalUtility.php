<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-6-22
 * Time: 下午2:08
 */

namespace app\common\utilities;

use app\common\models\PrimeKey;


class GlobalUtility {
    public static function maxPrimeKey($tableName)
    {
        $result = PrimeKey::findOne(['table_name' => $tableName]);

        return $result->max;
    }

    public static function isValidEmail($email)
    {
        $pattern = '\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)* ';
        return mb_ereg_match($pattern, $email);
    }

    public static function isValidPhone($phone)
    {
        $pattern = '^((\(\d{2,3}\))|(\d{3}\-))?13\d{9}$ ';
        return mb_ereg_match($pattern, $phone);
    }

} 