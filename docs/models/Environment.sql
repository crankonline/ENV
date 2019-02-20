-- ---------------------------------------------------------------------------------------

DROP DATABASE IF EXISTS "Environment";

-- ---------------------------------------------------------------------------------------

CREATE DATABASE
    "Environment"
WITH
    ENCODING = 'UTF8';

-- ---------------------------------------------------------------------------------------

-- DATA STRUCTURE AND RELATED OBJECTS:

-- ---------------------------------------------------------------------------------------

CREATE SCHEMA "Core";

-- ---------------------------------------------------------------------------------------

CREATE SEQUENCE "Core"."SEQ_UserRole_IDUserRole"
    INCREMENT BY 1;

CREATE TABLE "Core"."UserRole" (
    "IDUserRole"
        INT
        NOT NULL
        DEFAULT NEXTVAL('"Core"."SEQ_UserRole_IDUserRole"'::regclass),

    "Name"
        VARCHAR(80)
        NOT NULL,

    CONSTRAINT "PK_UserRole"
        PRIMARY KEY ("IDUserRole"),

    CONSTRAINT "UNQ_UserRole_Name"
        UNIQUE ("Name")
);

ALTER SEQUENCE "Core"."SEQ_UserRole_IDUserRole"
    OWNED BY "Core"."UserRole"."IDUserRole";

COMMENT ON TABLE
    "Core"."UserRole"
IS
    'Роли пользователей в системе.';

COMMENT ON COLUMN
    "Core"."UserRole"."IDUserRole"
IS
    'Счетчик.';

COMMENT ON COLUMN
    "Core"."UserRole"."Name"
IS
    'Наименование.';

-- ---------------------------------------------------------------------------------------

CREATE SEQUENCE "Core"."SEQ_User_IDUser"
    INCREMENT BY 1;

CREATE TABLE "Core"."User" (
    "IDUser"
        INT
        NOT NULL
        DEFAULT NEXTVAL('"Core"."SEQ_User_IDUser"'::regclass),

    "UserRoleID"
        INT
        NOT NULL,

    "Login"
        VARCHAR(30)
        NOT NULL,

    "Surname"
        VARCHAR(25)
        NOT NULL,

    "Name"
        VARCHAR(20)
        NOT NULL,

    "MiddleName"
        VARCHAR(25)
        NULL,

    "Phone"
        VARCHAR(255)
        NULL,

    "IsActive"
        BOOLEAN
        NOT NULL
        DEFAULT TRUE,

    "Password"
        BYTEA
        NOT NULL,

    "IsPasswordExpired"
        BOOLEAN
        NOT NULL
        DEFAULT TRUE,

    CONSTRAINT "PK_User"
        PRIMARY KEY ("IDUser"),

    CONSTRAINT "UNQ_User_Login"
        UNIQUE ("Login")
);

ALTER SEQUENCE "Core"."SEQ_User_IDUser"
    OWNED BY "Core"."User"."IDUser";

COMMENT ON TABLE
    "Core"."User"
IS
    'Учетные записи пользователей.';

COMMENT ON COLUMN
    "Core"."User"."IDUser"
IS
    'Счетчик.';

COMMENT ON COLUMN
    "Core"."User"."UserRoleID"
IS
    'Роль пользователя в системе.';

COMMENT ON COLUMN
    "Core"."User"."Login"
IS
    'Псевдоним для идентификации.';

COMMENT ON COLUMN
    "Core"."User"."Surname"
IS
    'Фамилия.';

COMMENT ON COLUMN
    "Core"."User"."Name"
IS
    'Имя.';

COMMENT ON COLUMN
    "Core"."User"."MiddleName"
IS
    'Отчество.';

COMMENT ON COLUMN
    "Core"."User"."Phone"
IS
    'Контактные телефоны.';

COMMENT ON COLUMN
    "Core"."User"."IsActive"
IS
    'Может ли быть осуществлен вход по учетной записи.';

COMMENT ON COLUMN
    "Core"."User"."Password"
IS
    'Двоичное представление хэша пароля.';

COMMENT ON COLUMN
    "Core"."User"."IsPasswordExpired"
IS
    'Является ли пароль устаревшим - подлежащим замене пользователем.';

-- ---------------------------------------------------------------------------------------

CREATE SEQUENCE "Core"."SEQ_UserVisit_IDUserVisit"
    INCREMENT BY 1;

CREATE TABLE "Core"."UserVisit" (
    "IDUserVisit"
        BIGINT
        NOT NULL
        DEFAULT NEXTVAL('"Core"."SEQ_UserVisit_IDUserVisit"'::regclass),

    "UserID"
        INT
        NOT NULL,

    "IpAddress"
        INET
        NOT NULL,

    "DateTime"
        TIMESTAMP
        NOT NULL
        DEFAULT NOW(),

    CONSTRAINT "PK_UserVisit"
        PRIMARY KEY ("IDUserVisit")
);

ALTER SEQUENCE "Core"."SEQ_UserVisit_IDUserVisit"
    OWNED BY "Core"."UserVisit"."IDUserVisit";

COMMENT ON TABLE
    "Core"."UserVisit"
IS
    'Визиты пользователей в систему.';

COMMENT ON COLUMN
    "Core"."UserVisit"."IDUserVisit"
IS
    'Счетчик.';

COMMENT ON COLUMN
    "Core"."UserVisit"."UserID"
IS
    'Оператор.';

COMMENT ON COLUMN
    "Core"."UserVisit"."IpAddress"
IS
    'IPv6 или IPv4, который зарегистрировал сервер при входе.';

COMMENT ON COLUMN
    "Core"."UserVisit"."DateTime"
IS
    'Дата и время входа.';

-- ---------------------------------------------------------------------------------------

CREATE SEQUENCE "Core"."SEQ_ModuleGroup_IDModuleGroup"
    INCREMENT BY 1;

CREATE TABLE "Core"."ModuleGroup" (
    "IDModuleGroup"
        INT
        NOT NULL
        DEFAULT NEXTVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),

    "Name"
        TEXT
        NOT NULL,

    CONSTRAINT "PK_ModuleGroup"
        PRIMARY KEY ("IDModuleGroup"),

    CONSTRAINT "UNQ_ModuleGroup_Name"
        UNIQUE ("Name")
);

ALTER SEQUENCE "Core"."SEQ_ModuleGroup_IDModuleGroup"
    OWNED BY "Core"."ModuleGroup"."IDModuleGroup";

COMMENT ON TABLE
    "Core"."ModuleGroup"
IS
    'Группы модулей системы.';

COMMENT ON COLUMN
    "Core"."ModuleGroup"."IDModuleGroup"
IS
    'Счетчик.';

COMMENT ON COLUMN
    "Core"."ModuleGroup"."Name"
IS
    'Наименование.';

-- ---------------------------------------------------------------------------------------

CREATE SEQUENCE "Core"."SEQ_Module_IDModule"
    INCREMENT BY 1;

CREATE TABLE "Core"."Module" (
    "IDModule"
        INT
        NOT NULL
        DEFAULT NEXTVAL('"Core"."SEQ_Module_IDModule"'::regclass),

    "ModuleGroupID"
        INT
        NULL,

    "AccessKey"
        TEXT
        NOT NULL,

    "HandlerClass"
        TEXT
        NOT NULL,

    "Name"
        TEXT
        NOT NULL,

    "IsEntryPoint"
        BOOLEAN
        NOT NULL
        DEFAULT FALSE,

    CONSTRAINT "PK_Module"
        PRIMARY KEY ("IDModule"),

    CONSTRAINT "UNQ_Module_AccessKey"
        UNIQUE ("AccessKey"),

    CONSTRAINT "UNQ_Module_HandlerClass"
        UNIQUE ("HandlerClass"),

    CONSTRAINT "UNQ_Module_Name"
        UNIQUE ("Name")
);

ALTER SEQUENCE "Core"."SEQ_Module_IDModule"
    OWNED BY "Core"."Module"."IDModule";

COMMENT ON TABLE
    "Core"."Module"
IS
    'Модули системы.';

COMMENT ON COLUMN
    "Core"."Module"."IDModule"
IS
    'Счетчик.';

COMMENT ON COLUMN
    "Core"."Module"."ModuleGroupID"
IS
    'Группа модулей.';

COMMENT ON COLUMN
    "Core"."Module"."AccessKey"
IS
    'Ключ для просмотра.';

COMMENT ON COLUMN
    "Core"."Module"."HandlerClass"
IS
    'Обслуживающий класс.';

COMMENT ON COLUMN
    "Core"."Module"."Name"
IS
    'Наименование.';

COMMENT ON COLUMN
    "Core"."Module"."IsEntryPoint"
IS
    'Является ли точкой входа для других модулей.';

-- ---------------------------------------------------------------------------------------

CREATE SEQUENCE "Core"."SEQ_ModulePermission_IDModulePermission"
    INCREMENT BY 1;

CREATE TABLE "Core"."ModulePermission" (
    "IDModulePermission"
        INT
        NOT NULL
        DEFAULT NEXTVAL('"Core"."SEQ_ModulePermission_IDModulePermission"'::regclass),

    "ModuleID"
        INT
        NOT NULL,

    "Mark"
        TEXT
        NOT NULL,

    "Name"
        TEXT
        NOT NULL,

    CONSTRAINT "PK_ModulePermission"
        PRIMARY KEY ("IDModulePermission"),

    CONSTRAINT "UNQ_ModulePermission_Module_Mark"
        UNIQUE ("ModuleID", "Mark"),

    CONSTRAINT "UNQ_ModulePermission_Module_Name"
        UNIQUE ("ModuleID", "Name")
);

ALTER SEQUENCE "Core"."SEQ_ModulePermission_IDModulePermission"
    OWNED BY "Core"."ModulePermission"."IDModulePermission";

COMMENT ON TABLE
    "Core"."ModulePermission"
IS
    'Разрешения для доступа к возможностям системы.';

COMMENT ON COLUMN
    "Core"."ModulePermission"."IDModulePermission"
IS
    'Счетчик.';

COMMENT ON COLUMN
    "Core"."ModulePermission"."ModuleID"
IS
    'Модуль.';

COMMENT ON COLUMN
    "Core"."ModulePermission"."Mark"
IS
    'Метка в коде.';

COMMENT ON COLUMN
    "Core"."ModulePermission"."Name"
IS
    'Наименование.';

-- ---------------------------------------------------------------------------------------

CREATE TABLE "Core"."UserRoleModulePermission" (
    "UserRoleID"
        INT
        NOT NULL,

    "ModulePermissionID"
        INT
        NOT NULL,

    CONSTRAINT "PK_UserRoleModulePermission"
        PRIMARY KEY ("UserRoleID", "ModulePermissionID")
);

COMMENT ON TABLE
    "Core"."UserRoleModulePermission"
IS
    'Возможности ролей пользователей.';

COMMENT ON COLUMN
    "Core"."UserRoleModulePermission"."UserRoleID"
IS
    'Роль.';

COMMENT ON COLUMN
    "Core"."UserRoleModulePermission"."ModulePermissionID"
IS
    'Возможность.';

-- ---------------------------------------------------------------------------------------

-- FOREIGN KEYS AND RELATED INDICES:

-- ---------------------------------------------------------------------------------------

CREATE INDEX
    "IDX_User_UserRole"
ON
    "Core"."User" ("UserRoleID");

ALTER TABLE "Core"."User"
    ADD CONSTRAINT "FK_User_UserRole"
        FOREIGN KEY ("UserRoleID")
        REFERENCES "Core"."UserRole" ("IDUserRole")
            ON UPDATE CASCADE
            ON DELETE RESTRICT;

-- ---------------------------------------------------------------------------------------

CREATE INDEX
    "IDX_UserVisit_User"
ON
    "Core"."UserVisit" ("UserID");

ALTER TABLE "Core"."UserVisit"
    ADD CONSTRAINT "FK_UserVisit_User"
        FOREIGN KEY ("UserID")
        REFERENCES "Core"."User" ("IDUser")
            ON UPDATE CASCADE
            ON DELETE CASCADE;

-- ---------------------------------------------------------------------------------------

CREATE INDEX
    "IDX_Module_ModuleGroup"
ON
    "Core"."Module" ("ModuleGroupID");

ALTER TABLE "Core"."Module"
    ADD CONSTRAINT "FK_Module_ModuleGroup"
        FOREIGN KEY ("ModuleGroupID")
        REFERENCES "Core"."ModuleGroup" ("IDModuleGroup")
            ON UPDATE CASCADE
            ON DELETE SET NULL;

-- ---------------------------------------------------------------------------------------

CREATE INDEX
    "IDX_ModulePermission_Module"
ON
    "Core"."ModulePermission" ("ModuleID");

ALTER TABLE "Core"."ModulePermission"
    ADD CONSTRAINT "FK_ModulePermission_Module"
        FOREIGN KEY ("ModuleID")
        REFERENCES "Core"."Module" ("IDModule")
            ON UPDATE CASCADE
            ON DELETE CASCADE;

-- ---------------------------------------------------------------------------------------

CREATE INDEX
    "IDX_UserRoleModulePermission_UserRole"
ON
    "Core"."UserRoleModulePermission" ("UserRoleID");

ALTER TABLE "Core"."UserRoleModulePermission"
    ADD CONSTRAINT "FK_UserRoleModulePermission_UserRole"
        FOREIGN KEY ("UserRoleID")
        REFERENCES "Core"."UserRole" ("IDUserRole")
            ON UPDATE CASCADE
            ON DELETE CASCADE;

-- ---------------------------------------------------------------------------------------

CREATE INDEX
    "IDX_UserRoleModulePermission_ModulePermission"
ON
    "Core"."UserRoleModulePermission" ("ModulePermissionID");

ALTER TABLE "Core"."UserRoleModulePermission"
    ADD CONSTRAINT "FK_UserRoleModulePermission_ModulePermission"
        FOREIGN KEY ("ModulePermissionID")
        REFERENCES "Core"."ModulePermission" ("IDModulePermission")
            ON UPDATE CASCADE
            ON DELETE CASCADE;

-- ---------------------------------------------------------------------------------------

DROP SCHEMA IF EXISTS "public";

-- ---------------------------------------------------------------------------------------

-- PREDEFINED DATA INSERTION:

-- ---------------------------------------------------------------------------------------

START TRANSACTION;

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."UserRole"
    ("IDUserRole", "Name")
VALUES
    (DEFAULT, 'Cудо');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."User"
    (
        "IDUser",
        "UserRoleID",
        "Login",
        "Surname",
        "Name",
        "MiddleName",
        "Phone",
        "IsActive",
        "Password",
        "IsPasswordExpired"
    )
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_UserRole_IDUserRole"'::regclass),
        'administrator',
        'Администратор',
        'Системы',
        NULL,
        NULL,
        DEFAULT,
        DECODE('52147617628f888c4a303d45e7d0c23de1c2ca50d5b5c37c1fe6b2f10531e5eb', 'hex'),
        DEFAULT
    );

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."ModuleGroup"
    ("IDModuleGroup", "Name")
VALUES
    (DEFAULT, 'Система');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'users',
        'Users',
        'Учетные записи',
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
        'modify-user',
        'ModifyUser',
        'Изменение учетной записи',
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

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'register-user',
        'RegisterUser',
        'Регистрация учетной записи',
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

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'user-roles',
        'UserRoles',
        'Роли учетных записей',
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
        'modify-user-role',
        'ModifyUserRole',
        'Изменение роли',
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

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'register-user-role',
        'RegisterUserRole',
        'Регистрация роли',
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

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'user-role-members',
        'UserRoleMembers',
        'Участники роли',
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

INSERT INTO "Core"."ModuleGroup"
    ("IDModuleGroup", "Name")
VALUES
    (DEFAULT, 'Клиенты');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'clients-list',
        'ClientsList',
        'Полный перечень клиентов',
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
        'requisites',
        'Requisites',
        'Профиль службы реквизитов',
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
        'sochi',
        'Sochi',
        'Профиль СОчИ (текущий)',
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
        'seo',
        'Seo',
        'Профиль СОчИ (старый)',
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
        'nwa',
        'Nwa',
        'Данные внешних источников',
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
        'pin-info',
        'PinInfo',
        'Информация по ПИН',
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
    (DEFAULT, 'Онлайн заявка');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'statements-received',
        'StatementsReceived',
        'Поступившие заявки',
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
        'statements-processed',
        'StatementsProcessed',
        'Обработанные заявки',
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
        'statement',
        'Statement',
        'Заявка',
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
    ),
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-view-files',
        'Просмотр файлов'
    ),
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-view-pins',
        'Просмотр PIN-кодов'
    ),
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-approve',
        'Утверждение'
    ),
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-identify',
        'Идентификация'
    ),
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-confirm-payment',
        'Подтверждение оплаты'
    ),
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-complete',
        'Завершение'
    ),
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-reject',
        'Отклонение'
    ),
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_Module_IDModule"'::regclass),
        'can-remove',
        'Удаление'
    );

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."ModuleGroup"
    ("IDModuleGroup", "Name")
VALUES
    (DEFAULT, 'ЭЦП');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'eds-expirations',
        'EdsExpirations',
        'Сертификаты с истекающим сроком действия',
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
        'eds-and-devices',
        'EdsAndDevices',
        'Сертификаты и устройства',
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
        'pki-search',
        'PkiSearch',
        'Поиск сертификатов в PKI',
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
    (DEFAULT, 'Сводные данные');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'aggregate-activities',
        'AggregateActivities',
        'Клиенты по видам деятельности',
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
        'aggregate-activity-details',
        'AggregateActivityDetails',
        'Клиенты по виду деятельности',
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

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'aggregate-reports',
        'AggregateReports',
        'Клиентская отчетность',
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
        'client-registration-statistics',
        'ClientRegistrationStatistics',
        'Cтатистика регистрации клиентов',
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
    (DEFAULT, 'Ядро');

-- ---------------------------------------------------------------------------------------

INSERT INTO "Core"."Module"
    ("IDModule", "ModuleGroupID", "AccessKey", "HandlerClass", "Name", "IsEntryPoint")
VALUES
    (
        DEFAULT,
        CURRVAL('"Core"."SEQ_ModuleGroup_IDModuleGroup"'::regclass),
        'api-calls',
        'ApiCalls',
        'Вызовы',
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
        'api-call',
        'ApiCall',
        'Данные вызова',
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

INSERT INTO "Core"."UserRoleModulePermission"
    ("UserRoleID", "ModulePermissionID")
SELECT
    CURRVAL('"Core"."SEQ_UserRole_IDUserRole"'::regclass),
    "p"."IDModulePermission"
FROM
    "Core"."ModulePermission" as "p"
ORDER BY
    1, 2;

-- ---------------------------------------------------------------------------------------

COMMIT;

-- ---------------------------------------------------------------------------------------