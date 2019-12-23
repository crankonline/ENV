<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Requisites\Data\Import\Data\Common;

use Environment\Soap\Types\Shared\Utils as Utils;

class Representative {
    const
        ROLES_CHIEF            = 1,
        ROLES_ACCOUNTANT       = 2,
        ROLES_EDS_RECEIVER     = 3,
        ROLES_EDS_USER         = 4,
        ROLES_CONSULTING_AGENT = 5,
        ROLES_CONSULTING_ROOT  = 6;

    const
        EDS_USAGE_MODEL_LOCAL = 1,
        EDS_USAGE_MODEL_CLOUD = 2;

    public
        $person,
        $edsUsageModel,
        $position,
        $roles,
        $phone,
        $deviceSerial;

    public static function create(array & $values){
        $self = new self();

        $self->person = Person::create($values);

        if(empty($values['position'])){
            throw new \Exception('Должность не указана.');
        } else {
            $self->position = (int)$values['position'];
        }

        if(empty($values['roles'])){
            throw new \Exception('Cотрудник не причислен ни к одной из ролей.');
        } else {
            $self->roles = (array)$values['roles'];
        }

        $isEdsRequired = (
            in_array(self::ROLES_CHIEF, $self->roles)
            ||
            in_array(self::ROLES_ACCOUNTANT, $self->roles)/*
            ||
            in_array(self::ROLES_EDS_RECEIVER, $self->roles)
            ||
            in_array(self::ROLES_EDS_USER, $self->roles)*/
        );

        if($isEdsRequired){
            if(empty($values['eds-usage-model'])){
                throw new \Exception('Модель использования ЭЦП не указана');
            } else {
                $self->edsUsageModel = (int)$values['eds-usage-model'];
            }
        } else {
            $self->edsUsageModel = null;
        }

        $values['work-phone'] = isset($values['work-phone'])
            ? Utils::monoSpace($values['work-phone'])
            : null;

        if(!$values['work-phone']){
            throw new \Exception('Рабочий телефон не указан.');
        } elseif(preg_match('/[^\d\s]/', $values['work-phone'])) {
            throw new \Exception('Рабочий телефон содержит неверные символы.');
        } else {
            $self->phone = $values['work-phone'];
        }

        if($self->edsUsageModel == self::EDS_USAGE_MODEL_LOCAL){
            if(empty($values['device-serial'])){
                throw new \Exception('Серийный номер устройства не указан');
            } elseif(!preg_match('/^\d{10,10}$/', $values['device-serial'])) {
                throw new \Exception('Серийный номер устройства имеет неверный формат.');
            } else {
                $self->deviceSerial = $values['device-serial'];
            }
        } else {
            $self->deviceSerial = null;
        }

        return $self;
    }
}
?>