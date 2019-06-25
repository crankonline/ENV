<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Shared;

class Utils {
    public static function dateReformat($from, $to, $date){
        $date = \DateTime::createFromFormat($from, $date);

        return $date ? $date->format($to) : null;
    }

    public static function dateIso8601ToIso9075($date, $withTime = true){
        return static::dateReformat(
            \DateTime::ISO8601,
            $withTime ? 'Y-m-d H:i:s' : 'Y-m-d',
            $date
        );
    }

    public static function dateToIso8601($format, $date, $withTime = true){
        return static::dateReformat(
            $format,
            $withTime ? \DateTime::ISO8601 : 'Y-m-d',
            $date
        );
    }

    public static function noSpace($string){
        return preg_replace('/\s+/', '', $string);
    }

    public static function monoSpace($string){
        return trim(preg_replace('/\s+/', ' ', $string));
    }
}
?>