# Changelog

## v1.1

[Full List of core changes since 1.0.1](https://github.com/fuel/core/compare/v1.0.1...v1.1)

### System changes

* Deprication of `Request::show_404()`, replaced with `throw new HttpNotFoundException` that has a handle method to show the 404
* Support for `handle()` method that is run when an exception isn't caught before `Error::exception_handler()` catches it.
* Support for special `_404_` route now in `public/index.php` thus no longer part of the core but still supported as a 'official default'
* Closures are now also supported in routes, thus routing to a Closure instead of a controler/method uri. Also added support for any type of callable in Route extensions you write yourself.
* Closure support in all getters & setters: if you get a value and also input a default the default can also be a Closure and you'll get the result of that. For setters the input can also be a closure and the result of the Closure will be set. (except for `View::set()` as one might want to pass a closure to the View)
* Moved the Environment setting from the `app/config/config.php` file to the `app/bootstrap.php` file.
* All `factory()` methods have been renamed to `forge()`.  This name better states the method's function.  The `factory()` methods are still there for backwards compatibility, but are deprecated and will log warning messages when used.
* The `$this->response` Response object is now deprecated.  Your action methods should return either a string, View object, ViewModel object or a Response object.
* Added the `fuel/app/vendor` directory to the default install.
* You can now have an unlimited number of sub-directories for your controllers. (e.g. `classes/controller/admin/users/groups.php` with a class name of `Controller_Admin_Users_Groups` would be at `site.com/admin/users/groups`)
* There is no longer a default controller for directories.  It used to be that going to something like `site.com/admin` would bring up `Controller_Admin_Admin` in `classes/controller/admin/admin.php`.  Now you must place that controller at it's expected location `classes/controller/admin.php` with a name of `Controller_Admin`.
* A `Controller::after()` method now gets passed the response of the controller, it must return that response (or modified) as well.
* Added new *function* `get_real_class()` to which you can pass a classname and it will return the actual class, to be used on classes of which you're not sure whether it is an alias or not.
* Module routes are prepended to the routes array when Fuel detects the fist URI segment as a module, therefor parsing them before an `(:any)` route in the app config.
* Config is now environment aware and allows partial/full overwriting of the base config from subdirectories in the config dir named after the environment.
* Added a new `Theme` class.  It allows you to easily add Theme support to your applications.
* `Fuel_Exception` has been renamed to `FuelException`
* `Fuel::find_file()` and related methods are now deprecated.  Use the `Finder` class instead (e.g. `Finder::search()`).
* Migrations are now supported in Modules and Packages
* Routing has 3 new shortcuts:
	* `:almun` matches all utf-8 alphabetical and numeric characters
	* `:num` matches all numeric characters.
	* `:alpha` matches all utf-8 alphabetical characters
* Put the `Autoloader` class into `Fuel\Core` to allow extending it, it must now be required in the app bootstrap file which is also the location where you must require your own extension.

### Security related

* Added Fuel's own response object class `Fuel\Core\Response` to default whitelist in `app/config/config.php` of objects that aren't encoded on output by the View when passed.
* The `security.auto_encode_view_data` config option in `app/config/config.php` has been renamed to `security.auto_filter_output`.
* `stdClass` was part of the default whitelisted classes from output encoding, this was a bug and it has been removed.

### Specific classes

* __Arr__: Added methods `Arr::get()`, `Arr::set()` and `Arr::prepend()`.
* __Arr__: `Arr::element()` and `Arr::elements()` have been deprecated.  Use the new `Arr::get()` instead.
* __Database__: Using transactions will no longer prevent exceptions, exceptions are thrown and should be handled by the dev. The `Database_Transaction` class has been deprecated as it has little use because of this change.
* __File__: `File::read_dir()` (and related methods on Area and Directory handler) now return dirnames with directory separator suffix
* __Fieldset_Field__: Parsing of validation rules has been moved from `Fieldset_Field::add_rule()` to `Validaton::_find_fule()`, from the outside the method still works the same but notices for inactive rules are now only shown when running the validation.
* __Form__: Added inline error reporting, which must first be switched on in config and will replace an `{error_msg}` tag
* __Form__: New default form template which puts it inside a table.
* __Fuel__: Added `Fuel::value()` which checks if the given value is a Closure, and returns the result of the Closure if it is, otherwise, simply the value.
* __Image__: No longer throws `Fuel_Exception` for any type of exception but instead `RuntimeException`, `InvalidArguementException` and `OutOfBoundsException` where appropriate.
* __Input__: `Input::post(null)` doesn't work to get full post array anymore, just `Input::post()` without params - same for all other Input methods
* __Input__: `Input::get_post()` has been deprecated and replaced by `Input::param()`.  It now also includes PUT and DELETE variables.
* __Input / Uri__: `Uri::detect()` moved to `Input::uri()` as it is part of the input and thus should be part of the input class
* __Request__: You can now also do external requests through the Request class, for now only a curl driver: `Request::forge('http//url', 'curl')` or `Request::forge('http//url', array('driver' => 'curl', 'method' => 'post', 'params' => array())`.
* __Validation__: `Validation::errors()` is depricated and replaced by singular form `Validation::error()` to be more in line with other class methods
* __Validation__: New 3rd parameter added to `Validation::run()` that allows adding callables for the duration of the run.
* __View__: The view class has been refactored and works much better now.  Output filtering is vastly improved.
* __View__: `View::capture()` has been split into two protected instance methods: `process_file()` and `get_data()`.  You will need to update your View class extensions.
* __View__: `View::$auto_encode` has been removed.  It has been replaced but auto_filter, which is per-view instance.
* __ViewModel__: Refactored the class internals to work more transparently with the `View`.
* __ViewModel__: Deprecated `$this->_template` and renamed it to `$this->_view`.
* __ViewModel__: Updated to work with the refactored `View` class.  Added `$this->bind()`.
* __ViewModel__: Deprecated `$this->set_template()` and renamed it to `$this->set_view()`.
* __Html__: Removed (not deprecated) the following methods: `Html::h()`, `Html::br()`, `Html::hr()`, `Html::nbs()`, `Html::title()`, `Html::header()`.  You should simply write the HTML yourself.
* __Config__: Added Config file drivers for PHP, INI, JSON and Yaml.  They are detected by file extension (e.g. `Config::load('foo.yml')` will load and parse the Yaml).

### Packages

* __Auth__: Renamed default table name from `simpleusers` to `users`.
* __Auth__: Added config options for DB connection and table columns used for fetching the user.
* __Auth__: Removed default config for groups & roles in `simpleauth.php` config file, only commented out examples left.
* __Orm__: Lots of tweaks to `Observer_Validation` related to changes to `Validation` & `Fieldset_Field` classes. Also changed it to only save properties that are actually changed.
* __Orm__: The `ValidationFailed` thrown when the `Observer_Validation` fails now includes a reference to the Fieldset instance that failed: `$valfailed->get_fieldset();`
* __Orm__: Added support for changing the type of join used when fetching relations, example: `Model_Example::query()->related('something', array('join_type' => 'inner'))->get();`
* __Orm__: Observers are no longer singleton but one instance per model with per model settings, check docs for more info.
* __Parser__: Added Parser package to the default install.
* __Parser__: Mustache is now part of the Parser package by default.  Version 0.7.1.
* __Email__: The Email package is added.

## v1.0

### Core

[Full Changelog](https://github.com/fuel/core/compare/v1.0-rc3...v1.0)

### Auth

[Full Changelog](https://github.com/fuel/auth/compare/v1.0-rc3...v1.0)

### Oil

[Full Changelog](https://github.com/fuel/oil/compare/v1.0-rc3...v1.0)

### Orm

[Full Changelog](https://github.com/fuel/orm/compare/v1.0-rc3...v1.0)


## v1.0-RC3

### Core

[Full Changelog](https://github.com/fuel/core/compare/v1.0-rc2.1...v1.0-rc3)

### Auth

[Full Changelog](https://github.com/fuel/auth/compare/v1.0-rc2...v1.0-rc3)

### Oil

[Full Changelog](https://github.com/fuel/oil/compare/v1.0-rc2...v1.0-rc3)

### Orm

[Full Changelog](https://github.com/fuel/orm/compare/v1.0-rc2...v1.0-rc3)


## v1.0-RC2.1

### Core

* Fixed a security issue where the URI was not being properly sanitized.

## v1.0-RC2

### Core

* oil refine install now makes the config directory writable. (Dan Horrigan)
* Added auto-id to select fields (Kelly Banman)
* Fixed typo in ::analyze\_table (Frank de Jonge)
* replaced the regex that processes :segment in the Route class. closes #33. (Harro Verton)
* Closes #31: logic error caused the Crypt class to update the config when nothing is changed. (Harro Verton)
* Fixed up XML output so that singular versions of basenode names are used when a numeric value is provided as a key.XML doesn't like numeric keys and item, item, item is boring. Also moved formatting logic out of the REST library. (Phil Sturgeon)
* Added Format::to\_php(). (Phil Sturgeon)
* Updated Form config file to work with the Form class we've had for the past 3 months (oops). Fixes #93 (Jelmer Schreuder)
* Fixes #115: Form::button() now produces a &lt;button&gt; tag instead of &lt;input&gt; (Harro Verton)
* Fixed #116: Throw an error if File::update can't open the file for write (Harro Verton)
* Added a check to File::open\_file() to make sure $resource is a valid resource before we attempt to flock() (Harro Verton)
* Fixed badly named variable in profiler. (Phil Sturgeon)
* Show full file paths in the Install task. No security concern if you're already in the terminal. (Phil Sturgeon)
* Fixed bug in \Date::create\_from\_string() where the date produced would always be exactly one month behind the actual date. (Ben Corlett)
* updated the Crypt class to make the generation of the random keys more secure (Harro Verton)
* fixed error in Fuel::find\_file(), causing a PHP notice on repeated finds (Harro Verton)
* The DBUtil class now respects the table prefix if set (Fixes #103). (Dan Horrigan)
* If an empty string is passed to Format::factory('', 'xml') it will no longer error, just return an empty array. (Phil Sturgeon)
* Added PHPSecLib to vendor to provide encryption features if no crypto is available in PHP. (Harro Verton)
* Rewritten the crypto class to use AES256 encryption, and a HMAC-SHA256 tamper validation hash. (Harro Verton)
* Added Redis to the bootstrap. (Jelmer Schreuder)
* Made Inflector::camelize() return camelcased result again but the Inflector::classify() won't use it anymore and still respect underscores. (Jelmer Schreuder)
* Allow setting labels as array including attributes instead of just tring in form->add (Jeffery Utter)
* Fix Date class. strptime returns years since 1900 not 1901. Dates were a year in the future. (Jeffery Utter)
* Options wasn't being passed when adding a radio.. thus it wasn't making all the separate fields. (Jeffery Utter)
* fixes bug #96: advanced regex must use non greedy match to properly match segments (Harro Verton)
* fixes bug #99: PHP notice due to not-initialized property (Harro Verton)
* Using memory\_get\_peak\_usage() instead of memory\_get\_usage() for more reliable memory reporting. (Jelmer Schreuder)
* Form generation: Fixed issue with "type" attribute set for textareas and selects. Also prevented empty for="" attributes by ignoring null values. (Jelmer Schreuder)
* Moved page link creation into separate method for more flexibility (Kelly Banman)
* fixed broken database profiling (Harro Verton)
* Input::real\_ip() now returns "0.0.0.0" if IP detection fails (Harro Verton)
* Bugfix: hidden inputs created with the Fieldset class caused unending loops. (Jelmer Schreuder)
* Fixed a bug that caused the image library to refuse all image types. (Alexander Hill)
* Corrected typos in the image class. (Alexander Hill)
* Fuel::find\_file() now caches files found per request URI, instead of a global cache. (Harro Verton)
* Fixed a bug in the response constructor. Response body was not setting. (Dan Horrigan)
* Bugfix: Fieldset::build() didn't match Form::build() for which it should be an alias. (Jelmer Schreuder)
* Changed Controller\_Rest formatting methods from private to protected so they can be extended (Tom Arnfeld)
* Improved the Fieldset::repopulate() method to also take a Model or array instead of using the POST values. Will accept any array, ArrayAccess instance, Orm\Model or object with public properties. (Jelmer Schreuder)

### Auth

* Fixed an issue with the casing of the Simple-driver classnames. (Jelmer Schreuder)
* Fixed small bug in Auth check method. (Jelmer Schreuder)
* Bugfix: ACL rights merging went wrong because the base was a string instead of an array. (Jelmer Schreuder)

### Oil

* Updated scaffolding to work better with the new ORM package. Fix #81.
* Suppress the error message for PHPUnit in oil, if it can't load the file from include it should just error as usual. (Phil Sturgeon)
* Fixed PHPUnit, said it wasn't installed when it was. (Phil Sturgeon)
* Fix #85: Scaffolding still referred to ActiveRecord instead of Orm. (Phil Sturgeon)

### Orm

* Added to\_array() method to export current object as an array. Improved ArrayAccess and Iterable implementation to work with relations. (Jelmer Schreuder)
* Finished the unfinished \_\_clone() method. (Jelmer Schreuder)
* Fixes #84 - now an exception is thrown when an invalid Model classname is given to a relation. (Jelmer Schreuder)
* Implemented \_\_isset() and \_\_unset() magic methods for Orm\Model (Jelmer Schreuder)
* Moved Query object creation into its own method to allow the more accurate Model\_Example::query()->where()->get(). (Jelmer Schreuder)
* order\_by() didn't return $this with array input. (Jelmer Schreuder)
* Fixed issue with constructing new models without adding properties. (Jelmer Schreuder)
