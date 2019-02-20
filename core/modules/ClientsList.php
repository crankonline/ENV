<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections,
    Unikum\Core\DataLayer as Datalayer;

class ClientsList extends \Environment\Core\Module {
    const
        ROLES_CHIEF      = 1,
        ROLES_ACCOUNTANT = 2;

    const
        SUBSCRIBER_ID = 1;

    protected $config = [
        'template' => 'layouts/ClientsList/Default.html'
    ];

    protected function getClients(){
        $sql = <<<SQL
SELECT
    "c-rqst"."IDRequisites",
    TO_CHAR(
        "u-uid"."DateTime",
        'DD.MM.YYYY HH24:MI:SS'
    ) as "register-stamp",
    CONCAT_WS(
        ' ',
        COALESCE(
            "c-lf"."ShortName",
            "c-lf"."Name"
        ),
        "c-rqst"."Name"
    ) as "name",
    "c-rqst"."Inn" as "inn",
    CONCAT_WS(
        ' ',
        "c-rpsn-chief"."Surname",
        "c-rpsn-chief"."Name",
        "c-rpsn-chief"."MiddleName"
    ) as "chief-name",
    "c-pspt-chief"."Series" as "chief-passport-series",
    "c-pspt-chief"."Number" as "chief-passport-number",
    CONCAT_WS(
        ' ',
        "c-rpsn-acc"."Surname",
        "c-rpsn-acc"."Name",
        "c-rpsn-acc"."MiddleName"
    ) as "accountant-name",
    "c-pspt-acc"."Series" as "accountant-passport-series",
    "c-pspt-acc"."Number" as "accountant-passport-number",
    "u-s"."isActive" as "usage-status-activity",
    TO_CHAR(
        "u-s"."DateTime",
        'DD.MM.YYYY HH24:MI:SS'
    ) as "usage-status-date-time"
FROM
    "Common"."Requisites" as "c-rqst"
        INNER JOIN "Uid"."Uid" as "u-uid"
            ON "c-rqst"."UidID" = "u-uid"."IDUid"
        INNER JOIN "Common"."LegalFormCivilLegalStatus" as "c-lfcls"
            ON "c-rqst"."LegalFormCivilLegalStatusID" = "c-lfcls"."IDLegalFormCivilLegalStatus"
        INNER JOIN "Common"."LegalForm" as "c-lf"
            ON "c-lfcls"."LegalFormID" = "c-lf"."IDLegalForm"
        LEFT JOIN (
            "Common"."RequisitesRepresentative" as "c-rr-chief"
                INNER JOIN "Common"."Representative" as "c-rpsn-chief"
                    ON "c-rr-chief"."RepresentativeID" = "c-rpsn-chief"."IDRepresentative"
                INNER JOIN "Common"."RequisitesRepresentativeRole" as "c-rrr-chief"
                    ON "c-rpsn-chief"."IDRepresentative" = "c-rrr-chief"."RepresentativeID"
                INNER JOIN "Common"."RepresentativeRole" as "c-rrl-chief"
                    ON "c-rrr-chief"."RepresentativeRoleID" = "c-rrl-chief"."IDRepresentativeRole"
                INNER JOIN "Common"."Passport" as "c-pspt-chief"
                    ON "c-rpsn-chief"."PassportID" = "c-pspt-chief"."IDPassport"
            ) ON
                ("c-rqst"."IDRequisites" = "c-rr-chief"."RequisitesID")
                AND
                ("c-rqst"."IDRequisites" = "c-rrr-chief"."RequisitesID")
                AND
                ("c-rrl-chief"."IDRepresentativeRole" = :chiefRoleId)
        LEFT JOIN (
            "Common"."RequisitesRepresentative" as "c-rr-acc"
                INNER JOIN "Common"."Representative" as "c-rpsn-acc"
                    ON "c-rr-acc"."RepresentativeID" = "c-rpsn-acc"."IDRepresentative"
                INNER JOIN "Common"."RequisitesRepresentativeRole" as "c-rrr-acc"
                    ON "c-rpsn-acc"."IDRepresentative" = "c-rrr-acc"."RepresentativeID"
                INNER JOIN "Common"."RepresentativeRole" as "c-rrl-acc"
                    ON "c-rrr-acc"."RepresentativeRoleID" = "c-rrl-acc"."IDRepresentativeRole"
                INNER JOIN "Common"."Passport" as "c-pspt-acc"
                    ON "c-rpsn-acc"."PassportID" = "c-pspt-acc"."IDPassport"
            ) ON
                ("c-rqst"."IDRequisites" = "c-rr-acc"."RequisitesID")
                AND
                ("c-rqst"."IDRequisites" = "c-rrr-acc"."RequisitesID")
                AND
                ("c-rrl-acc"."IDRepresentativeRole" = :accountantRoleId)
        LEFT JOIN (
            "Uid"."UsageStatus" as "u-s"
                INNER JOIN (
                        SELECT
                            "UidID" as "uid-id",
                            MAX("IDUsageStatus") as "usage-status-id"
                        FROM
                            "Uid"."UsageStatus" as "u-s"
                        GROUP BY
                            1
                        ORDER BY
                            1 DESC
                    ) as "u-s-grouper" ON
                        ("u-s"."UidID" = "u-s-grouper"."uid-id")
                        AND
                        ("u-s"."IDUsageStatus" = "u-s-grouper"."usage-status-id")
            ) ON
                ("u-s"."UidID" = "u-s-grouper"."uid-id")
                AND
                ("u-s"."UidID" = "u-uid"."IDUid")
                AND
                ("u-s"."IDUsageStatus" = "u-s-grouper"."usage-status-id")
WHERE
    ("u-uid"."SubscriberID" = :subscriberId)
    AND
    "c-rqst"."IsActive"
ORDER BY
    "u-s"."isActive" DESC,
    "u-uid"."DateTime";
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute([
            'chiefRoleId'      => self::ROLES_CHIEF,
            'accountantRoleId' => self::ROLES_ACCOUNTANT,
            'subscriberId'     => self::SUBSCRIBER_ID
        ]);

        return $stmt;
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-clients-list.css';

        $this->variables->errors = [];

        try {
            $this->variables->clients = $this->getClients();
        } catch(\Exception $e) {
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>