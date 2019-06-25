<?php
/**
 * Reregister
 */
namespace Environment\Traits\Soap\Types;

trait Helper {
    private static function groupValuesBySections(array $values, array $sections){
        $result = [];

        foreach($values as $key => $value){
            $pos = strpos($key, '-');

            if($pos === false){
                $result[$key] = $value;
            } else {
                $section = substr($key, 0, $pos);
                $field   = substr($key, $pos + 1);

                if(in_array($section, $sections)){
                    if(!isset($result[$section])){
                        $result[$section] = [];
                    }

                    $result[$section][$field] = $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    public static function filterGroup(array $values, $prefix){
        $result = [];

        $prefix .= '-';
        $length = strlen($prefix);

        foreach($values as $key => $value){
            if(strpos($key, $prefix) === 0){
                $field = substr($key, $length);

                $result[$field] = $value;
            }
        }

        return $result;
    }

    public static function extractArray(array $values){
        $result = [];

        foreach($values as $key => $value){
            preg_match_all('/\d+$/', $key, $matches);

            if(!$matches){
                continue;
            }

            $index = $matches[0][0];

            if(!isset($result[$index])){
                $result[$index] = [];
            }

            $key = preg_replace('/\-\d+$/', '', $key);

            $result[$index][$key] = $value;
        }

        return array_values($result);
    }
}
?>