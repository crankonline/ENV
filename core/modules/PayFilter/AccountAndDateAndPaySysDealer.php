<?php


namespace Environment\Modules\PayFilter;


class AccountAndDateAndPaySysDealer  extends \Environment\Modules\PayFilter\PayFilterDealer
{
    public function setParams($values) {
        $this->params = '"inv"."inn" LIKE :f_account AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max) AND "p"."PaymentSystemID" = :f_paymentSystem';
        $this->values = ([
            'f_account' => '%'.$values['account'].'%',
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax']),
            'f_paymentSystem'  => $values['paymentSystem']
        ]);
    }

}