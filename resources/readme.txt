-------------------------------------------------------------------------------------------

ЧТО ЭТО ЗА ДИРЕКТОРИЯ?

-------------------------------------------------------------------------------------------

Это директория, где содержатся ресурсные файлы для оформления и работы:
1. сценарии JavaScript;
2. каскадные таблицы стилей;
3. шрифты;
4. изображения;
и т. п.

-------------------------------------------------------------------------------------------

КОНВЕНЦИЯ ИМЕНОВАНИЯ И КОМПОНОВКИ ФАЙЛОВ:

-------------------------------------------------------------------------------------------

Допускается произвольная компоновка файлов и директорий.

-------------------------------------------------------------------------------------------

ПРИМЕРЫ:

-------------------------------------------------------------------------------------------

1. Для модуля core/modules/MyModule.php, если достаточно всего 1 шаблона:

resources/MyModule/my-style.css
resources/MyModule/my-ui.js

2. Для модуля core/modules/MyModule.php, если требуется 2 шаблона "Success" и "Failure":

resources/MyModule/shared-style.css
resources/MyModule/shared-ui.js
resources/MyModule/Success/specific-style.css
resources/MyModule/Success/specific-ui.js
resources/MyModule/Failure/specific-style.css
resources/MyModule/Failure/specific-ui.js

-------------------------------------------------------------------------------------------

КАКИЕ РАЗРЕШЕНИЯ НА ДОСТУП В ЭТУ ДИРЕКТОРИЮ?

-------------------------------------------------------------------------------------------

Запрещен листинг директории.
Обращение для конечных пользователей допустимо по прямым и относительным ссылкам на файлы.

-------------------------------------------------------------------------------------------
