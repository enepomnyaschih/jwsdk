    Copyright (C) 2011 Egor Nepomnyaschih
    
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.
    
    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

---------------------------------------------------------------------



Установка

0.  Для работы jWidget необходимо:
    -   PHP 5.2.9+
    -   Apache 2.2+
    -   Java (для запуска YUICompressor'а при релизной компиляции)
1.  Скопируйте папку jwidget в директорию вашего веб-приложения
2.  Настройте Apache на директорию jwidget/public, и установите параметр
    AllowOverride All
3.  Скопируйте yuicompressor.jar
    (http://yuilibrary.com/download/yuicompressor/)
    в директорию jwidget/build
4.  (необязательно) Чтобы некоторые тесты не упали, нужно установить PHP модуль
    для Apache, поскольку тесты используют PHP-экшены

----

Сборка проекта

Перейдите в директорию jwidget/build и запустите команду
php build.php <режим>
Пример:
php build.php debug

----

Запуск проекта

Включите Apache и откройте страницу http://localhost/tests

----

Список режимов

debug
    Отладочный режим. К странице подключаются несжатые исходные JS-файлы.
    В результате в директории jwidget/public/pages должны появиться html-файлы.
release
    Релизный режим. Все JS-файлы объединяются по JS-спискам, сжимаются и
    подключаются к странице. Эквивалентен последовательному запуску compress
    и link. В результате в директории jwidget/public/build должны появиться
    min.js-файлы, а в директории jwidget/public/pages - html-файлы.
compress
    Все JS-файлы объединяются по JS-спискам и сжимаются. В результате в
    директории jwidget/public/build должны появиться min.js-файлы.
link
    Предполагается, что папка jwidget/public/build уже содержит все необходимые
    min.js-файлы. Они подключаются к странице. В результате в директории
    jwidget/public/pages должны появиться html-файлы.

Замечание: release и compress выполняются долго из-за операции сжатия скриптов.

----

Используются следующие сторонние библиотеки (см. директорию public/thirdparty)
-   jQuery (http://jquery.com/)
-   jQuery.template
-   date.js
-   date.format.js
-   json2.js
-   md5.js
-   reset.css

Кроме того, для сжатия JS-файлов требуется
-   YUI Compressor

----

Особенности библиотеки

1.  Весь функционал, поставляемый библиотекой jWidget, подразделяется на
    3 категории:
    1)  пространство имен JW, включающее в себя утилитарные функции и классы
        для создания веб-приложений
    2)  дополнения к прототипам стандартных классов Array, Function, String и
        Date, включающие множество утилитарных методов
    3)  SDK для сборки проекта
2.  Библиотека предоставляет:
    1)  богатый набор утилитарных функций
    2)  базу для создания и наследования классов - JW.Class
    3)  собственную реализацию шаблона Observer - JW.Observable
    4)  классы коллекций (расширенный Array, JW.Map, JW.Dimap, JW.Collection)
    5)  детектор браузера JW.Browsers
    6)  адаптер для Ajax-запросов на основе $.ajax и JW.Observable -
        JW.Request, JW.Action и JW.RequestRepeater
    7)  шаблон сериализации объектов модели - JW.Model
    8)  класс таймера - JW.Timer
    9)  шаблон для реализации UI-компонентов - JW.UI.Component и JW.UI.Plugin
    10) многое другое

----

Стандарты кодирования

1.  Глобальных переменных нет
2.  Все классы приложения должны содержаться в определенном пространстве имен
3.  Каждый класс - в отдельном файле
4.  Пространства имен и классы именуются так: JustAnotherClass
5.  Публичные поля и методы именуются так: justAnotherMethod (camel)
6.  Все приватные поля и методы начинаются с _: _justAnotherField
7.  Все компоненты и классы модели принимают в конструктор объект конфигурации,
    все поля которого переносятся в экземпляр создаваемого объекта
8.  Все поля описываются так:
    justAnotherField: defaultValue, // модификаторы тип[, описание]
    Пример:
    userBoxEls: null, // [readonly] Array(4) of jQuery element
9.  Модификаторы:
    [required] - обязательная опция конфигурации
    [optional] - необязательная опция конфигурации
    [property] - свойство
    [readonly] - свойство только для чтения
10. Все типы запросов к серверу объявлены в виде экземпляров класса JW.Action
    и сгруппированы по файлам
11. jQuery-элементы именуются на justAnotherEl или justOtherEls
12. Новые UI-компоненты образуются путем наследования от других компонентов
    (хотя бы от JW.UI.Component)
13. Все HTML-шаблоны вынесены за пределы класса компонента и объявлены в блоке
    JW.UI.template(JustAnotherComponentClass, { ... });

----

Тесты

1.  Рекомендуется перед использованием библиотеки запустить в браузере тесты:
    http://localhost/tests. Стабильно работает пока только в Firefox (в других
    браузерах не реализован вывод стека вызовов)
2.  Тесты - лучший способ понять, как работают предоставленные утилиты. В
    тестах можно отыскать ответы на многие интересующие вас вопросы
3.  Вы можете с легкостью создать свой собственный тест-план: используйте
    public/tests и build/config/pages/tests.json в качестве примера
4.  Тесты созданы на основе библиотеки JW.Unit. Преимущества по сравнению с
    другими аналогичными тестирующими фреймворками:
    1)  По API очень близко напоминает FlexUnit
    2)  Можно создавать произвольные иерархии TestSuit'ов
    3)  TestSuit'ы создаются автоматически по пространствам имен, содержащих
        TestCase'ы
    4)  Поддерживаются асинхронные обработчики событий: async, forbid и sleep
    5)  Поддерживается тестирование на основе "ожидаемого вывода" (expected
        output)
    6)  Создание тестов упрощается за счет наличия универсальной функции
        сравнения переменных JW.equal
    7)  Для всякого подкласса TestSuit и TestCase можно определить методы
        setupAll, setup, teardown и teardownAll. Методы setup и teardown
        вызываются для каждого отдельного элемента внутри класса, а методы
        setupAll и teardownAll вызываются всего один раз до и после всех тестов
        внутри класса
    8)  Простой, но удобный пользовательский интерфейс
