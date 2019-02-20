-- ---------------------------------------------------------------------------------------

START TRANSACTION;

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        (
            SELECT
                "IDModuleGroup"
            FROM
                "Core"."ModuleGroup"
            WHERE
                ("Name" = 'Сводные данные')
        ),
        'aggregate-regions-sti',
        'AggregateRegionsSti',
        'Клиенты по районным УГНС',
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

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        (
            SELECT
                "IDModuleGroup"
            FROM
                "Core"."ModuleGroup"
            WHERE
                ("Name" = 'Сводные данные')
        ),
        'aggregate-region-sti-details',
        'AggregateRegionStiDetails',
        'Клиенты по районному УГНС',
        FALSE
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