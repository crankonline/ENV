-- ---------------------------------------------------------------------------------------

START TRANSACTION;

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."ModuleGroup"
    ("IDModuleGroup", "Name")
VALUES
    (DEFAULT, 'Сервисы');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'service-zero-report',
        'ServiceZeroReport',
        'Автоотправка нулевых отчетов',
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