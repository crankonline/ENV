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
                "IDModule"
            FROM
                "Core"."Module"
            WHERE
                ("AccessKey" = 'curator-sti')
        ),
        'can-clear-processing',
        'Очистка протокола проверки'
    );

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."ModulePermission"
    ("IDModulePermission", "ModuleID", "Mark", "Name")
VALUES
    (
        DEFAULT,
        (
            SELECT
                "IDModule"
            FROM
                "Core"."Module"
            WHERE
                ("AccessKey" = 'curator-sti')
        ),
        'can-change-region',
        'Замена УГНС-адресата'
    );

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."ModulePermission"
    ("IDModulePermission", "ModuleID", "Mark", "Name")
VALUES
    (
        DEFAULT,
        (
            SELECT
                "IDModule"
            FROM
                "Core"."Module"
            WHERE
                ("AccessKey" = 'curator-nsc')
        ),
        'can-clear-processing',
        'Очистка протокола проверки'
    );

-- ---------------------------------------------------------------------------------------

COMMIT;

-- ---------------------------------------------------------------------------------------