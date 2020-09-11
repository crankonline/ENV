<?php


namespace Environment\Modules\PayFilter;


class PaySysAndDate extends \Environment\Modules\PayFilter\PayFilter
{
    public function setParams($values) {
         $this->params = '"p"."PaymentSystemID" = :f_paymentSystem AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax']),
            'f_paymentSystem'  => $values['paymentSystem']
        ]);
    }


}