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



Installation

0.  jWidget prerequisites:
    -   PHP 5.2.9+
    -   Apache 2.2+
    -   Java (to run YUICompressor on release building)
1.  Copy "jwidget" folder into your web-application directory
2.  Configure Apache to refer "jwidget/public" directory, and set parameter
    "AllowOverride All"
3.  Copy yuicompressor.jar
    (http://yuilibrary.com/download/yuicompressor/)
    into directory "jwidget/build"
4.  (optional) To make all tests pass properly, you must install PHP Apache
    module, because tests use PHP actions

----

Project building

Change directory to "jwidget/build" and run command
php build.php <mode>
Example:
php build.php debug

----

Project running

Turn Apache on and open page http://localhost/tests

----

Modes list

debug
    Debug mode. Uncompressed source JS-files are attached to the page. As
    result, "jwidget/public/pages" folder must be populated with html-files.
release
    Release mode. All JS-files are merged by JS-lists, compressed and
    attached to the page. The same as consequent run of "compress"
    and "link". As result, "jwidget/public/build" must be populated with
    min.js-files, and "jwidget/public/pages" - with html-files.
compress
    All JS-files are merged by JS-lists and compressed. As result,
    "jwidget/public/build" must be populated with min.js-files.
link
    Requires "jwidget/public/build" populated with neccessary min.js-files
    already. They are attached to the page. As result "jwidget/public/pages"
    must be populated with html-files.

Notice that "release" and "compress" are slow because of JS files compression.

----

The next third-party libraries are used (see "public/thirdparty" folder)
-   jQuery (http://jquery.com/)
-   jQuery.template
-   date.js
-   date.format.js
-   json2.js
-   md5.js
-   reset.css

Also, neccessary for JS-files compression
-   YUI Compressor

----

Library features

1.  All jWidget functionality is split into 3 categories:
    1)  JW namespace, which consists of utility functions and classes for
        web-applications development
    2)  Standard classes' Array, Function, String and Date prototype
        extensions, which include a lot of utility methods
    3)  SDK for project building
2.  Library offers:
    1)  rich set of utility functions
    2)  classes creation and extention base - JW.Class
    3)  another Observer pattern implementation - JW.Observable
    4)  collection classes (extended Array, JW.Map, JW.Dimap, JW.Collection)
    5)  browser detector JW.Browsers
    6)  Ajax-requests adapter based on $.ajax and JW.Observable -
        JW.Request, JW.Action and JW.RequestRepeater
    7)  model serialization template - JW.Model
    8)  timer class - JW.Timer
    9)  UI-components development template - JW.UI.Component and JW.UI.Plugin
    10) etc.

----

Coding standards

1.  No global variables
2.  All application classes are included into specific namespace
3.  One class per source file
4.  Namespaces and classes naming convention: JustAnotherClass
5.  Public fields and methods naming convention: justAnotherMethod (camel)
6.  Private fields and methods naming convention: _justAnotherField
7.  All components and model classes take configuration object into constructor
    so that all configuration fields are transferred into object instance
8.  Field definition convention:
    justAnotherField : defaultValue, // modificators type[, description]
    Example:
    userBoxEls : null, // [readonly] Array(4) of jQuery element
9.  Modificators:
    [required] - required configuration option
    [optional] - optional configuration option
    [property] - random-access property
    [readonly] - read-only property
10. All AJAX request types are defined in JW.Action class instances and grouped
    by files
11. jQuery-elements are named justAnotherEl or justOtherEls (for arrays)
12. New UI-components are defined via other components' extension
    (at least from JW.UI.Component)
13. All HTML-templates are defined outside component class in block
    JW.UI.template(JustAnotherComponentClass, { ... });

----

Tests

1.  We recommend to run tests in browser before framework usage:
    http://localhost/tests. Stable in Firefox only for now (stack trace output
    is not implemented for other browsers yet)
2.  Tests are the best utilities usage sample. You can find the answers to many
    quiestions right there
3.  You can create your own test plan easily: use "public/tests" and
    "build/config/pages/tests.json" as example
4.  Tests are based on JW.Unit. Advantages as opposed to other unit testing
    frameworks:
    1)  Has very similar API to FlexUnit
    2)  Arbitrary TestSuit-s hierarchy are supported
    3)  TestSuit-s are created automatically by namespaces containing
        TestCase-s
    4)  Asynchronous event handlers are supported: async, forbid and sleep
    5)  "Expected output"-based tests are supported
    6)  It is easy to create tests using universal comparison utility JW.equal
    7)  Every TestSuit and TestCase can contain methods setupAll, setup,
        teardown and teardownAll. Methods setup and teardown are called for
        each item inside this test unit, and methods setupAll and teardownAll
        are called only once for this test unit
    8)  Simple and convenient user interface
