-------------------------------------------------------------------------------------------

ЧТО ЭТО ЗА ДИРЕКТОРИЯ?

-------------------------------------------------------------------------------------------

Это директория, где содержатся исполняемые файлы ядра и модулей системы.

-------------------------------------------------------------------------------------------

ЧТО ТУТ ЕСТЬ?

-------------------------------------------------------------------------------------------

Вложенные директории:

1. core          = директория базовых классов-компонентов ядра системы.
2. core-sf       = директория базовых классов-компонентов ядра системы.
3. modules       = директория модулей системы - частей движка, отвечающих за рендеринг
                   содержимого БД и файловой системы, а так же, возможно,
                   методов воздействия на это содержимое.
4. data-layers   = директория классов-прослоек для работы с СУБД - частей движка,
                   отвечающих за взаимодействие и воздействие на БД.
5. soap-services = директория SOAP-сервисов системы - частей движка, отвечающих за воздействие на
                   содержимое БД и файловой системы, а так же, возможно, вывод / выдачу.
6. soap-types    = директория общих подтипов для SOAP-сервисов - типы, которые
                   соответственно WSDL могут быть использованы несколькими сервисами.

Вложенные сценарии:

1. consts.php        = сценарий определения настроек и базовых констант.
2. configuration.php = сценарий общей конфигурации.

-------------------------------------------------------------------------------------------

КАКИЕ РАЗРЕШЕНИЯ НА ДОСТУП В ЭТУ ДИРЕКТОРИЮ?

-------------------------------------------------------------------------------------------

Прямой доступ конечных пользователей в эту директорию должен быть запрещен.

-------------------------------------------------------------------------------------------