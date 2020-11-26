<?php


namespace Environment\Modules\PayFilter;


class AccountDealer  extends \Environment\Modules\PayFilter\PayFilterDealer
{
    public function setParams(array $values) {
        $this->params = '"p"."Account" LIKE :f_account AND ("p"."DateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_account' => '%'.$values['account'].'%',
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])
        ]);
    }

}