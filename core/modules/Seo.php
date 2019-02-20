<?php
namespace Environment\Modules;

use Unikum\Core\Dbms\ConnectionManager as Connections;

class Seo extends \Environment\Core\Module {
    protected $config = [
        'template'   => 'layouts/Seo/Default.html',
        'listen'     => 'action'
    ];

    protected function getUser($inn){
        $params = [];
        $values = [];

        if($inn) {
            $params[] = '"su-u"."inn" = :inn';

            $values['inn'] = $inn;
        }

        $params = $params ? 'WHERE ' . implode(' AND ', $params) : null;

        $sql = <<<SQL
SELECT
    "su-u"."id" as "id",
    "su-u"."inn" as "inn",
    TO_CHAR("su-u"."registerdate", 'DD.MM.YYYY HH24:MI:SS') as "register-date",
    "su-u"."okpo" as "okpo",
    "su-u"."reg_num_sf" as "rnsf",
    TRIM("su-u"."company_name") as "company-name",
    CASE
        WHEN "su-addr-j"."id" IS NULL
            THEN
                TRIM("su-u"."address_old")
        ELSE
            CONCAT_WS(
                ', ',
                NULLIF(TRIM("su-addr-j"."postcode"), ''),
                NULLIF(TRIM("su-addr-j"."region"), ''),
                NULLIF(TRIM("su-addr-j"."settlement"), ''),
                NULLIF(TRIM("su-addr-j"."district"), ''),
                NULLIF(TRIM("su-addr-j"."street"), ''),
                NULLIF(TRIM("su-addr-j"."building"), ''),
                NULLIF(TRIM("su-addr-j"."apartment"), '')
            )
    END as "juristic-address",
    CASE
        WHEN "su-addr-p"."id" IS NULL
            THEN
                TRIM("su-u"."phaddress_old")
        ELSE
            CONCAT_WS(
                ', ',
                NULLIF(TRIM("su-addr-p"."postcode"), ''),
                NULLIF(TRIM("su-addr-p"."region"), ''),
                NULLIF(TRIM("su-addr-p"."settlement"), ''),
                NULLIF(TRIM("su-addr-p"."district"), ''),
                NULLIF(TRIM("su-addr-p"."street"), ''),
                NULLIF(TRIM("su-addr-p"."building"), ''),
                NULLIF(TRIM("su-addr-p"."apartment"), '')
            )
    END as "physical-address",
    TRIM("su-u"."chief_name") as "chief-name",
    TRIM("su-u"."chief_job") as "chief-position",
    TRIM("su-u"."chief_basis") as "chief-basis",
    TRIM("su-u"."account_name") as "accountant-name",
    "su-u"."contract_number" as "contract-number",
    TRIM("su-u"."work_tel") as "work-phone",
    TRIM("su-u"."person_tel") as "person-phone",
    "su-u"."e_mail" as "e-mail",
    CASE
        WHEN "su-bnk"."id" IS NULL
            THEN TRIM("su-u"."bank_name")
        ELSE
            "su-bnk"."short_name"
    END as "bank-name",
    CASE
        WHEN "su-bnk"."id" IS NULL
            THEN TRIM("su-u"."bank_bik")
        ELSE
            "su-bnk"."code"
    END as "bank-bic",
    TRIM("su-u"."bank_account") as "bank-account",
    TRIM("su-u"."responsible_person") as "responsible-person",
    "su-u"."resp_pers_tel" as "responsible-person-phone",
    "su-u"."gked" as "gked",
    "su-u"."soato" as "soato",
    "su-u"."pers_getds_name" as "eds-owner-name",
    "su-u"."pers_getds_tel" as "eds-owner-phone",
    "su-u"."pers_getds_job" as "eds-owner-job",
    CASE
        WHEN "su-pg-pass"."id" IS NULL
            THEN
                TRIM("su-u"."pers_getds_pas_old")
        ELSE
            CONCAT_WS(
                ', ',
                TRIM("su-pg-pass"."series") || ' ' || TRIM("su-pg-pass"."number"),
                NULLIF(TRIM("su-pg-pass"."issuing_authority"), ''),
                NULLIF(TO_CHAR("su-pg-pass"."issuing_date", 'DD.MM.YYYY'), '')
            )
    END as "eds-owner-passport",
    "su-u"."pers_useds_name" as "eds-user-name",
    "su-u"."pers_useds_tel" as "eds-user-phone",
    "su-u"."pers_useds_job" as "eds-user-job",
    CASE
        WHEN "su-pu-pass"."id" IS NULL
            THEN
                TRIM("su-u"."pers_useds_pas_old")
        ELSE
            CONCAT_WS(
                ', ',
                TRIM("su-pu-pass"."series") || ' ' || TRIM("su-pu-pass"."number"),
                NULLIF(TRIM("su-pu-pass"."issuing_authority"), ''),
                NULLIF(TO_CHAR("su-pu-pass"."issuing_date", 'DD.MM.YYYY'), '')
            )
    END as "eds-user-passport",
    "su-u"."region_sf" as "sf-region-code",
    COALESCE("sf-r"."name", 'Не указано') as "sf-region-name",
    "su-u"."tariff_sf" as "sf-tariff-id",
    COALESCE("sf-t"."description", 'Не указано') as "sf-tariff-name",
    "su-u"."region_gni" as "sti-region-main-code",
    COALESCE("sti-rgn-main"."name", 'Не указано') as "sti-region-main-name",
    "su-u"."region_gni_reciever" as "sti-region-receiver-code",
    COALESCE("sti-rgn-receiver"."name", 'Не указано') as "sti-region-receiver-name",
    "su-tt"."id" as "tariff-id",
    "su-tt"."code" as "tariff-code",
    "su-tt"."title" as "tariff-name"
FROM
    "seo_users"."users" as "su-u"
        LEFT JOIN "seo_users"."address" as "su-addr-j"
            ON "su-u"."address" = "su-addr-j"."id"
        LEFT JOIN "seo_users"."address" as "su-addr-p"
            ON "su-u"."phaddress" = "su-addr-p"."id"
        LEFT JOIN "seo_users"."bank" as "su-bnk"
            ON "su-u"."bank" = "su-bnk"."id"
        LEFT JOIN "seo_users"."passport" as "su-pg-pass"
            ON "su-u"."pers_getds_pas" = "su-pg-pass"."id"
        LEFT JOIN "seo_users"."passport" as "su-pu-pass"
            ON "su-u"."pers_useds_pas" = "su-pu-pass"."id"
        LEFT JOIN "reports_sf"."region" as "sf-r"
            ON "su-u"."region_sf" = "sf-r"."code"
        LEFT JOIN "reports_sf"."tariffs" as "sf-t"
            ON "su-u"."tariff_sf" = "sf-t"."tariff_code"
        LEFT JOIN "report_nalog"."region" as "sti-rgn-main"
            ON "su-u"."region_gni" = "sti-rgn-main"."code"
        LEFT JOIN "report_nalog"."region" as "sti-rgn-receiver"
            ON "su-u"."region_gni_reciever" = "sti-rgn-receiver"."code"
        LEFT JOIN "seo_users"."tariff_types" as "su-tt"
            ON "su-u"."tariff_code"::INT = "su-tt"."code"
{$params}
SQL;

        $stmt = Connections::getConnection('SeoBaseWeb')->prepare($sql);

        $stmt->execute($values);

        return $stmt->fetch();
    }

    protected function main(){
        $this->context->css[] = 'resources/css/ui-misc-form.css';
        $this->context->css[] = 'resources/css/ui-seo.css';

        $this->variables->errors = [];

        $inn = isset($_GET['inn']) ? $_GET['inn'] : null;

        if(!$inn){
            return;
        }

        if($inn && !preg_match('/^(\d{10,10})|(\d{14,14})$/', $inn)) {
            $this->variables->errors[] = 'ИНН должен состоять из 10 или 14 цифр';
            return;
        }

        try {
            $user = $this->getUser($inn);

            if(!$user){
                throw new \Exception('Пользователь не найден');
            }

            $this->variables->user = &$user;
        } catch(\Exception $e) {
            $this->variables->errors[] = $e->getMessage();
        }
    }
}
?>