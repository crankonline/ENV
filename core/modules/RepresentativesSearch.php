<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class RepresentativesSearch extends \Environment\Core\Module {
    protected $config = [
        'template' => 'layouts/RepresentativesSearch/Default.html'
    ];

    protected function getData(array $filters){
        $params = [];
        $values = [];

        if(!empty($filters['surname'])){
            $params[] = '("c-rp"."Surname" LIKE :rSurname)';

            $values['rSurname'] = $filters['surname'];
        }

        if(!empty($filters['name'])){
            $params[] = '("c-rp"."Name" LIKE :rName)';

            $values['rName'] = $filters['name'];
        }

        if(!empty($filters['middle-name'])){
            $params[] = '("c-rp"."MiddleName" LIKE :rMiddleName)';

            $values['rMiddleName'] = $filters['middle-name'];
        }

        if(!$params){
            return;
        }

        $params[] = '("c-p"."SubscriberID" = 1)';

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : '';

        $sql = <<<SQL
SELECT
    "c-p"."IDPassport" as "passport-id",
    "c-p"."Series" as "passport-series",
    "c-p"."Number" as "passport-number",
    "c-p"."IssuingAuthority" as "passport-issuing-authority",
    TO_CHAR("c-p"."IssuingDate", 'DD.MM.YYYY') as "passport-issuing-date",
    "c-rp"."Surname" as "representative-surname",
    "c-rp"."Name" as "representative-name",
    "c-rp"."MiddleName" as "representative-middle-name",
    STRING_AGG("c-rq"."Inn", ',') as "representative-companies"
FROM
    "Common"."Passport" as "c-p"
        INNER JOIN "Common"."Representative" as "c-rp"
            ON "c-p"."IDPassport" = "c-rp"."PassportID"
        INNER JOIN (
            "Common"."RequisitesRepresentative" as "c-rr"
                INNER JOIN "Common"."Requisites" as "c-rq"
                    ON
                        ("c-rr"."RequisitesID" = "c-rq"."IDRequisites")
                        AND
                        ("c-rq"."IsActive")
            ) ON "c-rp"."IDRepresentative" = "c-rr"."RepresentativeID"
{$params}
GROUP BY
    "c-p"."IDPassport", "c-rp"."IDRepresentative"
ORDER BY
    "c-rp"."Surname", "c-rp"."Name", "c-rp"."MiddleName";
SQL;

        $stmt = Connections::getConnection('Requisites')->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetchAll();
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-misc-stripes.css';
        $this->context->css[] = 'resources/css/ui-representatives-search.css';

        $this->variables->errors = [];

        if($_POST){
            try {
                $this->variables->data = $this->getData($_POST);
            } catch(\Exception $e) {
                $this->variables->errors[] = $e->getMessage();
            }
        }
    }
}
?>