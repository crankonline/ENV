<?php


namespace Environment\Modules\PayFilter;


class AccountAndDateAndPaySysAndType  extends \Environment\Modules\PayFilter\PayFilter
{
    public function setParams($values) {
        $this->params = '"p"."Account" LIKE :f_account AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max) AND "p"."Type" = :f_type AND "p"."PaymentSystemID" = :f_paymentSystem';
        $this->values = ([
            'f_account' => '%'.$values['account'].'%',
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax']),
            'f_type'  => $values['type'],
            'f_paymentSystem'  => $values['paymentSystem']
        ]);
    }

}