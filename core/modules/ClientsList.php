<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections,
    Unikum\Core\DataLayer as Datalayer;

class ClientsList extends \Environment\Core\Module {
    const
        ROLES_CHIEF      = 1,
        ROLES_ACCOUNTANT = 2;

    const ROWS_PER_PAGE = 30;

    const
        SUBSCRIBER_ID = 1;

    protected $config = [
        'template' => 'layouts/ClientsList/Default.html',
        'plugins'  => [
	        'paginator' => Plugins\Paginator::class
        ]
    ];

    protected function getClients(array $filters, $limit = null, $offset = null){
	    $sql = <<<SQL
SELECT
    COUNT(*)
FROM
    "Common"."Requisites" as "r-s"
WHERE 
	"r-s"."IsActive" = TRUE;

SQL;
	    $stmt = Connections::getConnection('Requisites')->prepare($sql);

	    $stmt->execute();

	    $count = $stmt->fetchColumn();

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
  "u-uid"."IDUid" "uid-id",
  (
    SELECT "isActive" FROM "Common"."Usage" WHERE "uid-id" =  "u-uid"."IDUid"
  ) "usage-status-activity",(
    SELECT    TO_CHAR(
        "DateTime",
        'DD.MM.YYYY HH24:MI:SS'
    ) FROM "Common"."Usage" WHERE "uid-id" =  "u-uid"."IDUid"
  ) "usage-status-date-time"
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

WHERE
  ("u-uid"."SubscriberID" = :subscriberId)
  AND
  "c-rqst"."IsActive"
ORDER BY
  "usage-status-activity" DESC,
  "u-uid"."DateTime"
LIMIT :limit OFFSET :offset;
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute([
            'chiefRoleId'      => self::ROLES_CHIEF,
            'accountantRoleId' => self::ROLES_ACCOUNTANT,
            'subscriberId'     => self::SUBSCRIBER_ID,
	        'limit'            => 30,
	        'offset'           => $offset
        ]);
	    $rows = $stmt->fetchAll();

//        return $stmt;
	    return [ &$count, &$rows ];
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-clients-list.css';

        $this->variables->errors = [];


	    $page   = isset($_GET['page']) ? (abs((int)$_GET['page']) ?: 1) : 1;
	    $limit  = self::ROWS_PER_PAGE;
	    $offset = ($page - 1) * $limit;

        try {
//            $this->variables->clients = $this->getClients();

	        list($count, $clients) = $this->getClients([], $limit, $offset);

	        $this->context->paginator['count'] = (int)ceil($count / $limit);

	        $this->variables->count = $count;
	        $this->variables->clients = &$clients;
        } catch(\Exception $e) {
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>