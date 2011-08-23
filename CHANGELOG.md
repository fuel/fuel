# Changelog

## v1.0.1

### Core

* Fixed #307 - Unrouted URIs could not have a colon in their URI followed by a letter, caused a 404 and multiple warnings. - Dan
* Removed Controller\_Template::before() param, always called without params thus no reason to expect any. Related #356 - Jelmer
* Fixed #333 - Html::img src attribute included index.php.  Thanks to @huglester for noticing this. - Dan
* Fixed an issue in Html::audio() where the source parameter defaulted to an array instead of an empty string. - Dan
* Fixed an issue with the autoloader where the namespace check was case-sensitive which caused issues with Modules with PascalCase namespaces. - Dan
* Fixed typo in Upload::process() marking all uploaded files valid. - Harro
* Added missing static keyword to Upload::get\_errors(). - Harro
* Upload::is\_valid() would remain false regardless of successful upload. - Jason Lewis
* Removed left-over email config file. - Harro
* Fixed a migration issue which would cause a PHP error when a destination version was not set. - Phil
* Fixed #360 - When no sub-namespace exists it would add a double // to the file path in the autoloader. - Dan
* Fixed an issue where is certain situations, there is no buffer, which causes notices to be dumped when you don't want them to be. - Dan
* Fixed #361: reference to Input class without calling it from global. - Jelmer
* Fixed #374: ensured there is a connection before starting SQL transactions, also fixed a lot of code style issues. - Jelmer
* Fixed Image resize\_crop() bugs - huglester
* Fixed typos in Image config - huglester
* Changed one config value as suggested per image class author - huglester
* Fixed Imagick class - huglester
* Fixed "Declaration of Fuel\Core\Image\_\*::\_rounded() should be compatible with..." and similar notices - huglester
* When doing multiple crops, the actual canvas width gets a little screwy in imagick.php (thanks dudeami) - huglester
* Bugfix - when using presets for resizing or crop resizing always getting black images because of bcdiv not calculating the ratios correctly due to type cast strangeness. - Rostislav Raykov
* Better handling of errors prior to Fuel::init() but after core bootstrap. - Dan
* Added break to switch which fixes a bug when 3 is passed, it returns 3rd3th - nbs
* Fixed #341 by suffixing a directory separator to all dirnames, making them all strings and preventing renumbering by array_merge and conflicts with file indexes with numeric dirnames.
* Fixed #338 - Added support for gzip encoding of output. - Jelmer
* Fixed #393 - Fieldset_Field options were set twice during construction, solved by @DregondRahl - Jelmer
* Removed the un-used Singleton class - Dan
* Rest controller response() method can take a string or int as a param but the empty() check makes it 404 on '0'. Fix this. - phil-lavin
* Fixed #399 - A proper 500 status header is sent back on fatal errors - Dan
* Added 'HTTP\_X\_CLUSTER\_CLIENT\_IP' to the list of things to check for in Input::real\_ip() to fix #403 - Tom Schlick
* Changed the way Upload::$valid is determined. is_valid() returns true if a validated file is present. - Harro
* Added Inflector::words\_to\_upper() that uppercases the words in an underscored classname and made the Router class use it when searching for a controller. - Jelmer

### Auth

* Auth BadFunctionCallException now has included class and function name so we know what's going on. - FrenkyNet
* Fixes #20 fixed instruction in comment for login driver creating and get\_user\_array. - FrenkyNet
* Added support for login with email or username instead of just username. Made the login $\_POST keys for login configurable. - FrenkyNet

### Oil

* Adding param to controller generator to stop singularizing class name - Aaron Kuzemchak
* oil g controller will now take plural or singular name for controller. - Phil
* Fixed another pluralisation issue. Class names for controllers were being singularised by Inflector::classify when built via Scaffolding. - Phil
* The standard notice message outputtery thing can now take an array too. - Phil

### Orm

* Empty array inside build_query was causing a notice - David Anderson
* Fixes #73 - PHP's current() function returns false on a false value and after the end of the array, the key() function can only return null when it's past the end of the array thus this check can't go wrong. (note: PHP accepts null as a key value but converts it to an empty string) - Jelmer
* DELETE & UPDATE queries ignored nested calls, this has now been fixed. - Jelmer
* Made Model::from\_array() more efficient. - Jelmer
* Allowing fetching with partial PK is just weird and shouldn't happen, also added comment to clarify what the first if condition is there for. - Jelmer
* Added support for hydration via DB::as_object - FrenkyNet

### Docs

* Fixed a lot of example, spelling, grammar and definition mistakes. - FrenkyNet and others

### Main Repo

* Added ob_callback config option, among other things to support gzip encoding of output. - Jelmer
* Updated error_reporting to -1 which is more future proof than E_ALL | E_STRICT - Jelmer


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
