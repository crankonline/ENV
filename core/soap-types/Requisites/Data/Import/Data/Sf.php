<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Requisites\Data\Import\Data;

class Sf {
    public
        $tariff,
        $region;

    public static function create(array & $values){
        $self = new self();

        if(empty($values['tariff'])){
            throw new \Exception('Вид тарифа не указан.');
        } else {
            $self->tariff = (int)$values['tariff'];
        }

        if(empty($values['region'])){
            throw new \Exception('Район не указан.');
        } elseif(!preg_match('/^\d{3,3}+$/', $values['region'])) {
            throw new \Exception('Район имеет неверный формат.');
        } else {
            $self->region = $values['region'];
        }

        return $self;
    }
}
?>