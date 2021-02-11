<?php
namespace Environment\Modules\ArrayExecToExcel;


class SystemDealer  extends \Environment\Modules\ArrayExecToExcel\ArrayExecToExcelDealer
{

    public function setParams(array $values) {
        $this->params = '"p"."PaymentSystemID" = :f_paymentSystem AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_paymentSystem' => $values['system'],
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])

        ]);

    }
}
