<?php
namespace Environment\Modules\ArrayExecToExcel;


class InnSystemDealer  extends \Environment\Modules\ArrayExecToExcel\ArrayExecToExcelDealer
{

    public function setParams(array $values) {
        $this->params = '"p"."PaymentSystemID" = :f_paymentSystem AND "inv"."inn" LIKE :f_inn AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_inn' => '%'.$values['inn'].'%',
            'f_paymentSystem' => $values['system'],
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])

        ]);

    }
}
