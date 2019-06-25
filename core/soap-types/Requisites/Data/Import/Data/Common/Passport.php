<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Requisites\Data\Import\Data\Common;

use Environment\Soap\Types\Shared\Utils as Utils;

class Passport {
    public
        $series,
        $number,
        $issuingAuthority,
        $issuingDate;

    public static function create(array & $values){
        $self = new self();

        $values['series'] = isset($values['series'])
            ? strtoupper(Utils::noSpace($values['series']))
            : null;

        if($values['series']){
            if(!preg_match('/^[A-Z0-9\-]+$/', $values['series'])){
                throw new \Exception('Cерия паспорта должна состоять только из латинских букв, цифр и символа тире.');
            } else {
                $self->series = $values['series'];
            }
        } else {
            throw new \Exception('Cерия паспорта не указана.');
        }

        $values['number'] = isset($values['number'])
            ? strtoupper(Utils::noSpace($values['number']))
            : null;

        if($values['number']){
            if(!preg_match('/^[A-Z0-9]+$/', $values['number'])){
                throw new \Exception('Номер паспорта должен состоять только из латинских букв и цифр.');
            } else {
                $self->number = $values['number'];
            }
        } else {
            throw new \Exception('Номер паспорта не указан.');
        }

        $values['issuing-authority'] = isset($values['issuing-authority'])
            ? Utils::monoSpace($values['issuing-authority'])
            : null;

        if($values['issuing-authority']){
            $self->issuingAuthority = $values['issuing-authority'];
        } else {
            throw new \Exception('Выдавший орган паспорта не указан.');
        }

        $values['issuing-date'] = isset($values['issuing-date'])
            ? Utils::dateToIso8601('d.m.Y', $values['issuing-date'])
            : null;

        if($values['issuing-date']){
            $self->issuingDate = $values['issuing-date'];
        } else {
            throw new \Exception('Дата выдачи паспорта не указана или имеет неверный формат.');
        }

        return $self;
    }
}
?>