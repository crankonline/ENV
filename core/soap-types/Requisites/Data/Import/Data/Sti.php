<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Requisites\Data\Import\Data;

class Sti {
    public
        $regionDefault,
        $regionReceive;

    public static function create(array & $values){
        $self = new self();

        if(empty($values['region-default'])){
            throw new \Exception('Район не указан.');
        } elseif(!preg_match('/^\d{3,3}+$/', $values['region-default'])) {
            throw new \Exception('Район имеет неверный формат.');
        } else {
            $self->regionDefault = $values['region-default'];
        }

        if(empty($values['region-receiver'])){
            $self->regionReceive = null;
        } else {
            if(preg_match('/^\d{3,3}+$/', $values['region-receiver'])) {
                $self->regionReceive = $values['region-receiver'];
            } else {
                throw new \Exception('Район сдачи имеет неверный формат.');
            }
        }

        return $self;
    }
}
?>