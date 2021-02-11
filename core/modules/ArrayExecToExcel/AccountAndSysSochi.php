<?php


namespace Environment\Modules\ArrayExecToExcel;


class AccountAndSysSochi extends \Environment\Modules\ArrayExecToExcel\ArrayExecToExcelSochi
{

    public function setParams(array $values) {
        $this->params = '"p"."Account" LIKE :f_account AND ("p"."PayDateTime" BETWEEN :f_d_min AND :f_d_max) AND "p"."PaymentSystemID" = :f_paymentSystem';
        $this->values = ([
            'f_account' => '%'.$values['account'].'%',
            'f_paymentSystem'  => $values['system'],
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])

        ]);

    }
}