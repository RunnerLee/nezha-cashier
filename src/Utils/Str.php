<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 2018-03
 */

namespace Runner\NezhaCashier\Utils;

class Str
{
    /**
     * @param $string
     *
     * @return string
     */
    public static function studly($string)
    {
        return ucfirst(str_replace(' ', '', lcfirst(ucwords(str_replace(['-', '_'], ' ', $string)))));
    }
}
