<?php
namespace Environment\Core;

abstract class Module extends \Unikum\Core\Module {
    const
        AK_USERS                          = 'users',
        AK_MODIFY_USER                    = 'modify-user',
        AK_REGISTER_USER                  = 'register-user',

        AK_USER_ROLES                     = 'user-roles',
        AK_MODIFY_USER_ROLE               = 'modify-user-role',
        AK_REGISTER_USER_ROLE             = 'register-user-role',
        AK_USER_ROLE_MEMBERS              = 'user-role-members',

        AK_CLIENT_LIST                    = 'clients-list',
        AK_REQUISITES                     = 'requisites',
        AK_SOCHI                          = 'sochi',
        AK_SEO                            = 'seo',
        AK_REPRESENTATIVES_EDITOR         = 'representatives-editor',
        AK_REPRESENTATIVES_SEARCH         = 'representatives-search',

        AK_STATEMENTS_RECEIVED            = 'statements-received',
        AK_STATEMENTS_PROCESSED           = 'statements-processed',
        AK_STATEMENT                      = 'statement',

        AK_EDS_EXPIRATIONS                = 'eds-expirations',
        AK_EDS_AND_DEVICES                = 'eds-and-devices',
        AK_PKI_SEARCH                     = 'pki-search',

        AK_AGGREGATE_ACTIVITIES           = 'aggregate-activities',
        AK_AGGREGATE_ACTIVITY_DETAILS     = 'aggregate-activity-details',
        AK_AGGREGATE_REPORTS              = 'aggregate-reports',
        AK_CLIENT_REGISTRATION_STATISTICS = 'client-registration-statistics',
        AK_AGGREGATE_REGIONS_STI          = 'aggregate-regions-sti',
        AK_AGGREGATE_REGION_STI_DETAILS   = 'aggregate-region-sti-details',

        AK_API_CALLS                      = 'api-calls',
        AK_API_CALL                       = 'api-call',

        AK_NWA                            = 'nwa',
        AK_PIN_INFO                       = 'pin-info',
        AK_UIN_PARSER                     = 'uin-parser',
        AK_UID_PARSER                     = 'uid-parser',
        AK_ACTIVITY_LIST                  = 'activity-list',

        AK_SERVICE_ZERO_REPORT            = 'service-zero-report',

        AK_MEDIA_SERVER                   = 'media-server',
        AK_PDF_DELIVERY_PERIOD            = 'pdf-delivery-period',

        AK_CURATOR_SF                     = 'curator-sf',
        AK_CURATOR_STI                    = 'curator-sti',
        AK_CURATOR_NSC                    = 'curator-nsc',

        AK_SERVICES                       = 'services',

        AK_DATA_HARVESTER                 = 'data-harvester',

        AK_REPORT_DECODE                  = 'report-decode',
        AK_SF_ARCHIVE                     = 'sf-archive',

        AK_SOCHI_REPORTING_FORMS          = 'sochi-reporting-forms',
        AK_SOCHI_EDIT_PERIOD_REPORTING    = 'sochi-edit-period-reporting',
        AK_SOCHI_ZERO_REPORT_ADMIN        = 'sochi-zero-report-admin',
        AK_SOCHI_EDIT_STI_REPORT          = 'sochi-edit-sti-report';
    const
        PMS_ACCESS = 'can-access';

    protected function isPermitted($accessKey, $permissionMark = null){
        if($permissionMark === null){
            $permissionMark = static::PMS_ACCESS;
        }

        if(isset($this->config->permissions)){
            $permissions = &$this->config->permissions;
        } else {
            $permissions = [];
        }

        return (
            isset(
                $permissions[$accessKey],
                $permissions[$accessKey][$permissionMark]
            )
            &&
            $permissions[$accessKey][$permissionMark]
        );
    }
}
?>