-- ---------------------------------------------------------------------------------------

START TRANSACTION;

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."ModulePermission"
    ("IDModulePermission", "ModuleID", "Mark", "Name")
VALUES
    (
        DEFAULT,
        (
            SELECT
                "c-m"."IDModule"
            FROM
                "Core"."Module" as "c-m"
            WHERE
                "c-m"."AccessKey" = 'requisites'
        ),
        'can-change-usage-status',
        'Может менять состояние обслуживания'
    );

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        (
            SELECT
                "c-mg"."IDModuleGroup"
            FROM
                "Core"."ModuleGroup" as "c-mg"
            WHERE
                ("c-mg"."Name" = 'Справочники')
        ),
        'data-harvester',
        'DataHarvester',
        'Cборщик данных по внешним источникам',
        TRUE
    );

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."ModulePermission"
    ("IDModulePermission", "ModuleID", "Mark", "Name")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-access',
        'Доступ к модулю'
    );

-- ---------------------------------------------------------------------------------------

COMMIT;

-- ---------------------------------------------------------------------------------------