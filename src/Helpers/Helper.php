<?php

namespace Mindk\Framework\Helpers;

use Mindk\Framework\Config\Config;


class Helper
{
    /**
     * Transliterate string
     * 
     * @param $s
     * @return mixed|string
     */
    public static function transliterate($s){
        $s = (string) $s;
        $s = strip_tags($s);
        $s = str_replace(["\n", "\r"], " ", $s);
        $s = preg_replace("/\s+/", ' ', $s);
        $s = trim($s);
        $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
        $s = strtr($s, ['а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e',
                        'ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k',
                        'л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
                        'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c',
                        'ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e',
                        'ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>'']);
        $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
        $s = str_replace(" ", "-", $s);

        return $s;
    }

    /**
     * Create absolute path
     * 
     * @param $path
     * @return string
     */
    public static function getPath($path){
        return $_SERVER['DOCUMENT_ROOT'] . $path;
    }
}