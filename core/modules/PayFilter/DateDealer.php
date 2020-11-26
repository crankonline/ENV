<?php


namespace  Environment\Modules\PayFilter;


class DateDealer extends \Environment\Modules\PayFilter\PayFilterDealer
{
    public function setParams(array $values) {
        $this->params = '("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])
        ]);
    }

}