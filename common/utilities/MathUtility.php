<?php
/**
 * Created by PhpStorm.
 * User: hly
 * Date: 14-5-22
 * Time: 下午7:43
 */

namespace app\common\utilities;


class MathUtility
{
    public static function fetchRandom($count = 5, $min = 1, $max = 100)
    {
        $num = 0;
        $count = min($count, $max - $min + 1);
        $return = array();
        while ($num < $count) {
            $return[] = mt_rand($min, $max);
            $return = array_flip(array_flip($return));
            $num = count($return);
        }
        shuffle($return);
        return $return;

//        return [
//            1,
//            2,
//        ];
    }
} 