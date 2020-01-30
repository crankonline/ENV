<?php
/**
 * Reregister
 */
namespace Environment\Soap\Clients\Requisites;

use Environment\Soap\Types\Requisites\Shared\Common\Activity as CommonActivity,
	Environment\Soap\Types\Requisites\Shared\Common\CapitalForm as CommonCapitalForm,
	Environment\Soap\Types\Requisites\Shared\Common\OwnershipForm as CommonOwnershipForm,
	Environment\Soap\Types\Requisites\Shared\Common\LegalForm as CommonLegalForm,
	Environment\Soap\Types\Requisites\Shared\Common\ManagementForm as CommonManagementForm,
    Environment\Soap\Types\Requisites\Shared\Common\CivilLegalStatus as CommonCivilLegalStatus,
    Environment\Soap\Types\Requisites\Shared\Common\ChiefBasis as CommonChiefBasis,
    Environment\Soap\Types\Requisites\Shared\Common\Region as CommonRegion,
    Environment\Soap\Types\Requisites\Shared\Common\District as CommonDistrict,
    Environment\Soap\Types\Requisites\Shared\Common\Settlement as CommonSettlement,
    Environment\Soap\Types\Requisites\Shared\Common\Bank as CommonBank,
    Environment\Soap\Types\Requisites\Shared\Common\EdsUsageModel as CommonEdsUsageModel,
    Environment\Soap\Types\Requisites\Shared\Common\Representative\Position as CommonRepresentativePosition,
    Environment\Soap\Types\Requisites\Shared\Common\Representative\Role as CommonRepresentativeRole,
    Environment\Soap\Types\Requisites\Shared\Sf\Tariff as SfTariff,
    Environment\Soap\Types\Requisites\Shared\Sf\Region as SfRegion,
    Environment\Soap\Types\Requisites\Shared\Sti\Region as StiRegion;

final class Meta extends \SoapClient {
    const WSDL = 'http://api.dostek.test/RequisitesMeta.php?wsdl';

    public function __construct(){
        $login    = 'api-' . date('z') . '-user';
        $password = 'p@-' . round(date('z') * 3.14 * 15 * 2.7245 / 4 + 448) . '$';

        $options = [
            'soap_version'       => SOAP_1_1,
            'exceptions'         => true,
            'trace'              => 1,
            'cache_wsdl'         => WSDL_CACHE_NONE,
            'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'connection_timeout' => 60,
            'classmap'           => [
                'CommonActivity'         => CommonActivity::class,
                'CommonCapitalForm'      => CommonCapitalForm::class,
                'CommonOwnershipForm'    => CommonOwnershipForm::class,
                'CommonLegalForm'        => CommonLegalForm::class,
                'CommonManagementForm'   => CommonManagementForm::class,
                'CommonCivilLegalStatus' => CommonCivilLegalStatus::class,
                'CommonChiefBasis'       => CommonChiefBasis::class,
                'CommonRegion'           => CommonRegion::class,
                'CommonDistrict'         => CommonDistrict::class,
                'CommonSettlement'       => CommonSettlement::class,
                'CommonBank'             => CommonBank::class,
                'CommonEdsUsageModel'    => CommonEdsUsageModel::class,

                'CommonRepresentativePosition' => CommonRepresentativePosition::class,
                'CommonRepresentativeRole'     => CommonRepresentativeRole::class,

                'SfTariff'  => SfTariff::class,
                'SfRegion'  => SfRegion::class,
                'StiRegion' => StiRegion::class
            ],

            'login'    => $login,
            'password' => $password
        ];

        parent::__construct($_ENV['soapClients_requisites_meta_wsdl'], $options);
    }
}
?>