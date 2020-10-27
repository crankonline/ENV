<?php


namespace Environment\Modules\PayFilter;


class AccountAndDateAndPaySysAndInnDealer  extends \Environment\Modules\PayFilter\PayFilterDealer
{
    public function setParams(array $values) {
        $this->params = '"p"."Account" LIKE :f_account AND  "inv"."inn" LIKE :f_inn AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max) AND "p"."PaymentSystemID" = :f_paymentSystem';
        $this->values = ([
            'f_account'        => '%'.$values['account'].'%',
            'f_inn'            => '%'.$values['inn'].'%',
            'f_d_min'          => $values['dateMin'],
            'f_d_max'          => $this->getDateMax($values['dateMax']),
            'f_paymentSystem'  => $values['paymentSystem']
        ]);
    }

}