<?php


namespace Environment\Modules\PayFilter;


class AccountSochi  extends \Environment\Modules\PayFilter\PayFilterSochi
{
    public function setParams(array $values) {
        $this->params = '"p"."Account" LIKE :f_account AND ("p"."PayDateTime" BETWEEN :f_d_min AND :f_d_max)';
        $this->values = ([
            'f_account' => '%'.$values['account'].'%',
            'f_d_min'  => $values['dateMin'],
            'f_d_max'  => $this->getDateMax($values['dateMax'])
        ]);
    }

}