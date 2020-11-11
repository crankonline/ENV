<?php

namespace Environment\Modules\ArrayExecToExcel;


class DateSochi extends \Environment\Modules\ArrayExecToExcel\ArrayExecToExcelSochi
{
    public function setParams(array $values) {
        $this->params = '("p"."PayDateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])

        ]);

    }


}