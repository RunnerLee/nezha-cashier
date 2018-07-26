<?php

namespace Runner\NezhaCashier\Utils;

class Xml
{
    /**
     * @param $string
     *
     * @return array
     */
    public static function fromXml($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * @param $array
     * @param string
     */
    public static function toXml($arr)
    {
        $xml = '<xml>';
        foreach ($arr as $key => $val) {
            $xml .= is_numeric($val) ? "<{$key}>{$val}</{$key}>" : "<{$key}><![CDATA[{$val}]]></{$key}>";
        }
        $xml .= '</xml>';

        return $xml;
    }
}
