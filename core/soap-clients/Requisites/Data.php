<?php
/**
 * Reregister
 */
namespace Environment\Soap\Clients\Requisites;

use Environment\Soap\Types\Requisites\Data\Import\Data as ImportData,
	Environment\Soap\Types\Requisites\Data\Import\Data\Common as ImportCommon,
	Environment\Soap\Types\Requisites\Data\Import\Data\Sf as ImportSf,
	Environment\Soap\Types\Requisites\Data\Import\Data\Sti as ImportSti,
	Environment\Soap\Types\Requisites\Data\Import\Data\Nsc as ImportNsc,
	Environment\Soap\Types\Requisites\Data\Import\Data\Common\Address as ImportCommonAddress,
	Environment\Soap\Types\Requisites\Data\Import\Data\Common\Passport as ImportCommonPassport,
	Environment\Soap\Types\Requisites\Data\Import\Data\Common\Person as ImportCommonPerson,
	Environment\Soap\Types\Requisites\Data\Import\Data\Common\Representative as ImportCommonRepresentative;

use Environment\Soap\Types\Requisites\Data\Export\Data as ExportData,
	Environment\Soap\Types\Requisites\Data\Export\Data\Common as ExportCommon,
	Environment\Soap\Types\Requisites\Data\Export\Data\Sf as ExportSf,
	Environment\Soap\Types\Requisites\Data\Export\Data\Sti as ExportSti,
	Environment\Soap\Types\Requisites\Data\Export\Data\Nsc as ExportNsc,
//    Reregister\Soap\Types\Requisites\Data\Export\Data\UsageStatus as ExportCUsageStatus,
    Environment\Soap\Types\Requisites\Data\Export\Data\Common\Address as ExportCommonAddress,
	Environment\Soap\Types\Requisites\Data\Export\Data\Common\Passport as ExportCommonPassport,
	Environment\Soap\Types\Requisites\Data\Export\Data\Common\Person as ExportCommonPerson,
	Environment\Soap\Types\Requisites\Data\Export\Data\Common\Representative as ExportCommonRepresentative;

//use Environment\Soap\Types\Requisites\Data\Export\Data\UsageStatus as ExportUsageStatus;

use Environment\Soap\Types\Requisites\Shared\Common\Activity as ExportCommonActivity,
	Environment\Soap\Types\Requisites\Shared\Common\CapitalForm as ExportCommonCapitalForm,
	Environment\Soap\Types\Requisites\Shared\Common\OwnershipForm as ExportCommonOwnershipForm,
	Environment\Soap\Types\Requisites\Shared\Common\LegalForm as ExportCommonLegalForm,
	Environment\Soap\Types\Requisites\Shared\Common\ManagementForm as ExportCommonManagementForm,
	Environment\Soap\Types\Requisites\Shared\Common\CivilLegalStatus as ExportCommonCivilLegalStatus,
	Environment\Soap\Types\Requisites\Shared\Common\ChiefBasis as ExportCommonChiefBasis,
	Environment\Soap\Types\Requisites\Shared\Common\Region as ExportCommonRegion,
	Environment\Soap\Types\Requisites\Shared\Common\District as ExportCommonDistrict,
	Environment\Soap\Types\Requisites\Shared\Common\Settlement as ExportCommonSettlement,
	Environment\Soap\Types\Requisites\Shared\Common\Bank as ExportCommonBank,
	Environment\Soap\Types\Requisites\Shared\Common\EdsUsageModel as ExportCommonEdsUsageModel,
	Environment\Soap\Types\Requisites\Shared\Common\Representative\Position as ExportCommonRepresentativePosition,
	Environment\Soap\Types\Requisites\Shared\Common\Representative\Role as ExportCommonRepresentativeRole,
	Environment\Soap\Types\Requisites\Shared\Sf\Tariff as ExportSfTariff,
	Environment\Soap\Types\Requisites\Shared\Sf\Region as ExportSfRegion,
	Environment\Soap\Types\Requisites\Shared\Sti\Region as ExportStiRegion;

final class Data extends \SoapClient {
    const WSDL  = 'http://api.dostek.test/RequisitesData.php?wsdl';

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
                'ImportData'   => ImportData::class,

                'ImportCommon' => ImportCommon::class,
                'ImportSf'     => ImportSf::class,
                'ImportSti'    => ImportSti::class,
                'ImportNsc'    => ImportNsc::class,

                'ImportCommonAddress'        => ImportCommonAddress::class,
                'ImportCommonPassport'       => ImportCommonPassport::class,
                'ImportCommonPerson'         => ImportCommonPerson::class,
                'ImportCommonRepresentative' => ImportCommonRepresentative::class,

                'ExportData'   => ExportData::class,

                'ExportCommon' => ExportCommon::class,
                'ExportSf'     => ExportSf::class,
                'ExportSti'    => ExportSti::class,
                'ExportNsc'    => ExportNsc::class,
//                'ExportUsageStatus' => ExportUsageStatus::class,

                'ExportCommonPassport'       => ExportCommonPassport::class,
                'ExportCommonPerson'         => ExportCommonPerson::class,
                'ExportCommonRepresentative' => ExportCommonRepresentative::class,

                'ExportCommonActivity'         => ExportCommonActivity::class,
                'ExportCommonCapitalForm'      => ExportCommonCapitalForm::class,
                'ExportCommonOwnershipForm'    => ExportCommonOwnershipForm::class,
                'ExportCommonLegalForm'        => ExportCommonLegalForm::class,
                'ExportCommonManagementForm'   => ExportCommonManagementForm::class,
                'ExportCommonCivilLegalStatus' => ExportCommonCivilLegalStatus::class,
                'ExportCommonChiefBasis'       => ExportCommonChiefBasis::class,
                'ExportCommonRegion'           => ExportCommonRegion::class,
                'ExportCommonDistrict'         => ExportCommonDistrict::class,
                'ExportCommonSettlement'       => ExportCommonSettlement::class,
                'ExportCommonAddress'          => ExportCommonAddress::class,
                'ExportCommonBank'             => ExportCommonBank::class,
                'ExportCommonEdsUsageModel'    => ExportCommonEdsUsageModel::class,

                'ExportCommonRepresentativePosition' => ExportCommonRepresentativePosition::class,
                'ExportCommonRepresentativeRole'     => ExportCommonRepresentativeRole::class,

                'ExportSfTariff' => ExportSfTariff::class,
                'ExportSfRegion' => ExportSfRegion::class,

                'ExportStiRegion' => ExportStiRegion::class
            ],

            'login'    => $login,
            'password' => $password
        ];

        parent::__construct($_ENV['soapClients_requisites_data_wsdl'], $options);
    }
}
?>