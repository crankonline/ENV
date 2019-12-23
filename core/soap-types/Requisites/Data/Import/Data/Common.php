<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Requisites\Data\Import\Data;

use Environment\Soap\Types\Shared\Utils as Utils;

class Common {
    use \Environment\Traits\Soap\Types\Helper;

    const
        CIVIL_LEGAL_STATUS_PHYSICAL = 2;

    public
        $mainActivity,
        $capitalForm,
        $legalForm,
        $managementForm,
        $civilLegalStatus,
        $chiefBasis;

    public
        $juristicAddress,
        $physicalAddress;

    public
        $name,
        $fullName,
        $inn,
        $okpo,
        $rnsf,
        $rnmj,
        $eMail,
        $bank,
        $bankAccount;

    public
        $representatives;

    public static function create(array & $values){
        $self = new self();

        if(empty($values['main-activity'])){
            throw new \Exception('Основной вид деятельности не указан.');
        } else {
            $self->mainActivity = (int)$values['main-activity'];
        }

        if(empty($values['legal-form'])){
            throw new \Exception('Организационно-правовая форма не указана.');
        } else {
            $self->legalForm = (int)$values['legal-form'];
        }

        if(empty($values['civil-legal-status'])){
            throw new \Exception('Гражданско-правовой статус не указан.');
        } else {
            $self->civilLegalStatus = (int)$values['civil-legal-status'];

            $values['rnmj'] = empty($values['rnmj'])
                ? null
                : Utils::noSpace($values['rnmj']);

            switch($self->civilLegalStatus){
                case self::CIVIL_LEGAL_STATUS_PHYSICAL:
                    if(!empty($values['capital-form'])){
                        throw new \Exception('Форма участия в капитале не указывается для физических лиц.');
                    }

                    if(!empty($values['management-form'])){
                        throw new \Exception('Форма правления не указывается для физических лиц.');
                    }

                    if($values['rnmj']){
                        throw new \Exception('Регистрационный номер Министерства Юстиции не указывается для физических лиц.');
                    }
                break;

                default:
                    if(empty($values['capital-form'])){
                        throw new \Exception('Форма участия в капитале не указана.');
                    } else {
                        $self->capitalForm = (int)$values['capital-form'];
                    }

                    if(empty($values['management-form'])){
                        throw new \Exception('Форма правления не указана.');
                    } else {
                        $self->managementForm = (int)$values['management-form'];
                    }

                    if($values['rnmj']){
                        if(!preg_match('/^\d+\-\d+-.+$/', $values['rnmj'])){
                            throw new \Exception('Регистрационный номер Министерства Юстиции имеет неверный формат.');
                        } else {
                            $self->rnmj = $values['rnmj'];
                        }
                    } else {
                        throw new \Exception('Регистрационный номер Министерства Юстиции не указан.');
                    }
                break;
            }
        }

        if(empty($values['chief-basis'])){
            throw new \Exception('Основание для занимаемой должности не указано.');
        } else {
            $self->chiefBasis = (int)$values['chief-basis'];
        }

        $jurValues  = self::filterGroup($values, 'juristic');
        $physValues = self::filterGroup($values, 'physical');

        $self->juristicAddress = Common\Address::create($jurValues);

        if($physValues){
            $self->physicalAddress = Common\Address::create($physValues);
        }

        $values['name'] = empty($values['name'])
            ? null
            : Utils::monoSpace($values['name']);

        if(empty($values['name'])){
            throw new \Exception('Наименование не указано.');
        } else {
            $self->name = $values['name'];
        }

        $values['full-name'] = empty($values['full-name'])
            ? null
            : Utils::monoSpace($values['full-name']);

        $self->fullName = $values['full-name'] ?: null;

        if(empty($values['inn'])){
            throw new \Exception('Код ИНН не указан.');
        } elseif(!preg_match('/^((\d{10,10})|(\d{14,14}))$/', $values['inn'])) {
            throw new \Exception('Код ИНН должен состоять из 10 или 14 цифр.');
        } else {
            $self->inn = $values['inn'];
        }

        if(empty($values['okpo'])){
            throw new \Exception('Код ОКПО не указан.');
        } elseif(!preg_match('/^((\d{8,8})|([A-Z]\d{7,7}))$/', $values['okpo'])) {
            throw new \Exception('Код ОКПО должен состоять из 8 цифр или 7 цифр с лидирующим латинским символом от A до Z.');
        } else {
            $self->okpo = $values['okpo'];
        }

        if(empty($values['rnsf'])){
            throw new \Exception('Регистрационный номер Cоциального Фонда не указан.');
        } elseif(!preg_match('/^\d{7,12}$/', $values['rnsf'])) {
            throw new \Exception('Регистрационный номер Cоциального Фонда должен содержать от 7 до 10 цифр.');
        } else {
            $self->rnsf = $values['rnsf'];
        }

        if(empty($values['email'])){
            throw new \Exception('Адрес электронной почты не указан.');
        } elseif(!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Адрес электронной почты имеет неверный формат.');
        } else {
            $self->eMail = $values['email'];
        }

        if(empty($values['bank-bic'])){
            $self->bank        = null;
            $self->bankAccount = null;
        } else {
            if(!preg_match('/^\d{6,6}$/', $values['bank-bic'])){
                throw new \Exception('Указанный БИК должен состоять из 6 цифр.');
            }

            if(!preg_match('/^\d{1,50}$/', $values['bank-account'])){
                throw new \Exception('Указанный расчетный счет должен содержать до 50 цифр.');
            }

            $self->bank        = $values['bank-bic'];
            $self->bankAccount = $values['bank-account'];
        }

        $chfValues = self::filterGroup($values, 'chief');
        $repValues = self::filterGroup($values, 'representative');

        unset($chfValues['basis']);

        if(empty($chfValues['roles'])){
            $chfValues['roles'] = [ Common\Representative::ROLES_CHIEF ];
        } else {
            $chfValues['roles'][] = Common\Representative::ROLES_CHIEF;
        }

        $representatives = self::extractArray($repValues);

        array_unshift($representatives, $chfValues);

        $self->representatives = [];

        foreach($representatives as $representative){
            $self->representatives[] = Common\Representative::create($representative);
        }

        $roles = [
            Common\Representative::ROLES_CHIEF => [
                'name'     => 'Руководитель',
                'required' => true,
                'count'    => 0
            ],
            Common\Representative::ROLES_ACCOUNTANT => [
                'name'     => 'Бухгалтер',
                'required' => false,
                'count'    => 0
            ],
            Common\Representative::ROLES_CONSULTING_AGENT => [
                'name'     => 'Консалтинговый агент',
                'required' => false,
                'count'    => 0
            ],
            Common\Representative::ROLES_CONSULTING_ROOT => [
                'name'     => 'Сотрудник корневой консалтинговой структуры',
                'required' => false,
                'count'    => 0
            ],
            Common\Representative::ROLES_EDS_RECEIVER => [
                'name'     => 'Лицо ответственное за получение ЭЦП',
                'required' => true,
                'count'    => 0
            ],
            Common\Representative::ROLES_EDS_USER => [
                'name'     => 'Лицо ответственное за использование ЭЦП',
                'required' => true,
                'count'    => 0
            ]
        ];

        foreach($self->representatives as $representative){
            foreach($representative->roles as $role){
                if(isset($roles[$role])){
                    $roles[$role]['count']++;
                } else {
                    throw new \Exception('Неизвестная роль представителя.');
                }
            }
        }

        foreach($roles as &$role){
            if(!$role['count'] && $role['required']){
                throw new \Exception('Роль "' . $role['name'] . '" не распределена.');
            } elseif($role['count'] > 1) {
                throw new \Exception('Роль "' . $role['name'] . '" может быть использована только единожды.');
            }
        }

        return $self;
    }
}
?>