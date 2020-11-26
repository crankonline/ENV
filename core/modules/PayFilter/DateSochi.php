<?php


namespace  Environment\Modules\PayFilter;


class DateSochi extends \Environment\Modules\PayFilter\PayFilterSochi
{
    public function setParams($values, $limits, $offset) {
        $this->params = '("p"."PayDateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax']),
            'limit'    => 50,
            'offset'   => $offset

        ]);

        $this->limits = $limits;
    }

}