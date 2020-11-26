<?php


namespace Environment\Modules\PayFilter;


class PaySysAndDateAndType extends \Environment\Modules\PayFilter\PayFilter
{

    public function setParams(array $values) {
        $this->params = '"p"."PaymentSystemID" = :f_paymentSystem AND "p"."Type" = :f_type AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax']),
            'f_type'   => $values['type'],
            'f_paymentSystem'   => $values['paymentSystem']
        ]);
    }
}