<?php
namespace Environment\Modules\ArrayExecToExcel;


class AccountInnDealer  extends \Environment\Modules\ArrayExecToExcel\ArrayExecToExcelDealer
{

    public function setParams(array $values) {
        $this->params = '"p"."Account" LIKE :f_account AND "inv"."inn" LIKE :f_inn AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_account' => '%'.$values['account'].'%',
            'f_inn' => '%'.$values['inn'].'%',
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])

        ]);

    }
}
