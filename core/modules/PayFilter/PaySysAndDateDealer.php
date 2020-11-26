<?php


namespace Environment\Modules\PayFilter;


class PaySysAndDateDealer extends \Environment\Modules\PayFilter\PayFilterDealer
{
    public function setParams(array $values) {
         $this->params = '"p"."PaymentSystemID" = :f_paymentSystem AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax']),
            'f_paymentSystem'  => $values['paymentSystem']
        ]);
    }


}