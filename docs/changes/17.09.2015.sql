-- ---------------------------------------------------------------------------------------

START TRANSACTION;

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."ModuleGroup"
    ("IDModuleGroup", "Name")
VALUES
    (DEFAULT, 'Справочники');

-- ---------------------------------------------------------------------------------------

UPDATE
    "Core"."Module"
SET
    "ModuleGroupID" = CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass)
WHERE
    "AccessKey" IN ('nwa', 'pin-info');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'uin-parser',
        'UinParser',
        'Информация по UIN',
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
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'uid-parser',
        'UidParser',
        'Информация по UID',
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
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'activity-list',
        'ActivityList',
        'Справочник видов деятельности',
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
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'country-passports',
        'CountryPassports',
        'Паспортные данные по стране',
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
                ("Name" = 'Клиенты')
        ),
        'representatives-editor',
        'RepresentativesEditor',
        'Редактор представителей',
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
                ("Name" = 'Клиенты')
        ),
        'representatives-search',
        'RepresentativesSearch',
        'Поиск представителей',
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
                ("Name" = 'Клиенты')
        ),
        'clients-complex-list',
        'ClientsComplexList',
        'Перечень консалтинговых клиентов',
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

INSERT INTO "Core"."ModuleGroup"
    ("IDModuleGroup", "Name")
VALUES
    (DEFAULT, 'Техническая поддержка');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'curator-sti',
        'CuratorSti',
        'Кураторское приложение ГНС',
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
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'curator-nsc',
        'CuratorNsc',
        'Кураторское приложение НСК',
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
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'curator-sf',
        'CuratorSf',
        'Кураторское приложение СФ',
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