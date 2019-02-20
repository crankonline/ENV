<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class ClientsComplexList extends \Environment\Core\Module {
    const
        ROLES_CONSULTING = 5,
        ROLES_ROOT       = 6;

    protected $config = [
        'template' => 'layouts/ClientsComplexList/Default.html'
    ];

    protected function getData(){
        $sql = <<<SQL
SELECT DISTINCT
    "c-pspt"."Series" as "passport-series",
    "c-pspt"."Number" as "passport-number",
    CONCAT_WS(
        ' ',
        "c-rpsn"."Surname",
        "c-rpsn"."Name",
        "c-rpsn"."MiddleName"
    ) as "representative-name",
    "sub-root"."company" as "company-root",
    "sub-bindings"."companies" as "company-bindings"
FROM
    "Common"."RequisitesRepresentative" as "c-rr"
        INNER JOIN "Common"."Representative" as "c-rpsn"
            ON "c-rr"."RepresentativeID" = "c-rpsn"."IDRepresentative"
        INNER JOIN "Common"."Passport" as "c-pspt"
            ON "c-rpsn"."PassportID" = "c-pspt"."IDPassport"
        LEFT JOIN (
            SELECT
                "c-rrr"."RepresentativeID" as "representative-id",
                "c-rqst"."Inn" as "company"
            FROM
                "Common"."RequisitesRepresentativeRole" as "c-rrr"
                    INNER JOIN "Common"."Requisites" as "c-rqst"
                        ON "c-rrr"."RequisitesID" = "c-rqst"."IDRequisites"
            WHERE
                ("c-rrr"."RepresentativeRoleID" = :repRoot)
                AND
                "c-rqst"."IsActive"
        ) as "sub-root"
            ON "c-rpsn"."IDRepresentative" = "sub-root"."representative-id"
        LEFT JOIN (
            SELECT
                "c-rrr"."RepresentativeID" as "representative-id",
                STRING_AGG("c-rqst"."Inn", ',') as "companies"
            FROM
                "Common"."RequisitesRepresentativeRole" as "c-rrr"
                    INNER JOIN "Common"."Requisites" as "c-rqst"
                        ON "c-rrr"."RequisitesID" = "c-rqst"."IDRequisites"
            WHERE
                ("c-rrr"."RepresentativeRoleID" = :repConsulting)
                AND
                "c-rqst"."IsActive"
            GROUP BY
                1
        ) as "sub-bindings"
            ON "c-rpsn"."IDRepresentative" = "sub-bindings"."representative-id"
WHERE
    ("c-pspt"."SubscriberID" = 1)
    AND
    (
        ("sub-root"."representative-id" IS NOT NULL)
        OR
        ("sub-bindings"."representative-id" IS NOT NULL)
    )
ORDER BY
    3;
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute([
            'repRoot'       => self::ROLES_ROOT,
            'repConsulting' => self::ROLES_CONSULTING
        ]);

        return $stmt->fetchAll();
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-clients-complex-list.css';

        $this->variables->errors = [];

        try {
            $this->variables->data = $this->getData();
        } catch(\Exception $e) {
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>