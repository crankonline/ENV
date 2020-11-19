<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Requisites\Data\Import\Data\Common;

use Environment\Soap\Types\Shared\Utils as Utils;

class Person {
    use \Environment\Traits\Soap\Types\Helper;

    public
        $passport,
        $surname,
        $name,
        $middleName,
        $pin;

    public static function create(array & $values){
        $self = new self();

        $pValues = self::filterGroup($values, 'passport');

        $self->passport = Passport::create($pValues);

        $values['surname'] = isset($values['surname'])
            ? Utils::monoSpace($values['surname'])
            : null;

        if($values['surname']){
            $self->surname = $values['surname'];
        } else {
            throw new \Exception('Фамилия сотрудника не указана.');
        }

        $values['name'] = isset($values['name'])
            ? Utils::monoSpace($values['name'])
            : null;

        if($values['name']){
            $self->name = $values['name'];
        } else {
            throw new \Exception('Имя сотрудника не указано.');
        }

        $self->middleName = isset($values['middle-name'])
            ? (Utils::monoSpace($values['middle-name']) ?: null)
            : null;

        $self->pin = isset($values['pin'])
            ? (Utils::monoSpace($values['pin']) ?: null)
            : null;

        return $self;
    }
}
?>
