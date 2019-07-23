<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Requisites\Data\Import\Data\Common;

use Environment\Soap\Types\Shared\Utils as Utils;

class Address {
    public
        $settlement,
        $postCode,
        $street,
        $building,
        $apartment;

    public static function create(array & $values){
        $self = new self();

        if(empty($values['settlement'])){
            throw new \Exception('Идентификатор населенного пункта не указан.');
        } elseif(!preg_match('/^\d+$/', $values['settlement'])) {
            throw new \Exception('Идентификатор населенного пункта имеет неверный формат.');
        } else {
            $self->settlement = $values['settlement'];
        }

        if(empty($values['post-code'])){
            throw new \Exception('Почтовый индекс не указан.');
        } elseif(!preg_match('/^\d{6,6}$/', $values['post-code'])) {
            throw new \Exception('Почтовый индекс имеет неверный формат.');
        } else {
            $self->postCode = $values['post-code'];
        }

        $values['street'] = isset($values['street'])
            ? Utils::monoSpace($values['street'])
            : null;

        if($values['street']){
            $self->street = $values['street'];
        } else {
            throw new \Exception('Улица не указана.');
        }

        $values['building'] = isset($values['building'])
            ? Utils::monoSpace($values['building'])
            : null;

        if($values['building']){
            if(mb_strlen($values['building'], 'UTF-8') > 20){
                throw new \Exception('Длина номера дома / строения не должна превышать 20 символов.');
            } else {
                $self->building = $values['building'];
            }
        } else {
            throw new \Exception('Номер дома / строения не указан.');
        }

        $values['apartment'] = isset($values['apartment'])
            ? (Utils::monoSpace($values['apartment']) ?: null)
            : null;

        if($values['apartment']){
            if(mb_strlen($values['apartment'], 'UTF-8') > 20){
                throw new \Exception('Длина номера квартиры / офиса не должна превышать 20 символов.');
            } else {
                $self->apartment = $values['apartment'];
            }
        } else {
            $self->apartment = null;
        }

        return $self;
    }
}
?>