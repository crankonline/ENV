<?php
/**
 * Reregister
 */
namespace Environment\Soap\Types\Requisites\Data\Import;

class Data {
    use \Environment\Traits\Soap\Types\Helper;

    public
        $common,
        $sf,
        $sti,
        $nsc;

    public static function create(array $values){
        $values = self::groupValuesBySections(
            $values,
            [ 'common', 'sf', 'sti', 'nsc' ]
        );

        $self = new self();

        if(isset($values['common'])){
            $self->common = Data\Common::create($values['common']);
        }

        if(isset($values['sf'])){
            $self->sf = Data\Sf::create($values['sf']);
        }

        if(isset($values['sti'])){
            $self->sti = Data\Sti::create($values['sti']);
        }

        if(isset($values['nsc'])){
            $self->nsc = Data\Nsc::create($values['nsc']);
        }

        return $self;
    }
}
?>