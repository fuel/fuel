# Changelog

## v1.8.2

### Security advisories

* [**SEC-CORE-009**]:  Unzip vulnerability to slip-attack!

See [the website](https://fuelphp.com/security-advisories) for more information about reported security issues and their status.

### Important fixes, changes, notes. Read them carefully.

* The code has been scanned for new warnings emitted by PHP 7.2 and 7.3.

### Important fixes, changes, notes. Read them carefully.

* The code has been scanned for new warnings emitted by PHP 7.2. and PHP 7.3. 

### Security related

See the advisories.

### Backward compatibility notes

* `Fieldset`: An exception is thrown if you try to `delete()` a Fieldset field that does not exist.

### System changes

* Htmlawed, used by `Security::clean()`, has been updated to v1.2.4.2, to provide PHP 7.3 compatibility.

### Specific classes

* Fixed a bug in `get_common_path()` returning incorrect results when the first path passed is an empty string.
* `DB`: Broken database cached results object has been fixed (1.8.1.1 hotfix).
* `DB`: Fixed `last_query()` no longer returning the last query after a call to `count_last_query()`.
* `DB`: Fixed database result iteration (1.8.1.4 hotfix). 
* `DB`: Still capture any PDO errors in the event these have been disabled in the PHP configuration.
* `DB`: New caching option on `query()` and a new `caching()` method allow you to enable/disable result caching on a per-query basis. NB: you need a result cache object if you need random access to database results.
* `Config`: You can now use dot-notation when specifying group names, so you can load configuration data at any level in the tree.
* `DB`: Fixed not being able to generate "ORDER BY group" due to a validation typo.
* `Crypt`: Replaced a PHP5.6+ function that creeped in by a coded alternative (1.8.1.3 hotfix).
* `ErrorHandler`: Added specific support for handling and displaying SoapFault error information.
* `Fieldset`: New `set_name()` allows to you change the fieldname of an existing Fielset field.
* `Fieldset`: An exception is thrown if you try to `delete()` a Fieldset field that does not exist.
* `Fieldset`: New `duplicate()` method allows you to clone an existing Fieldset field.
* `Image`: New `extension()` method returns the extension of the image file.
* `Input`: Fixed incorrectly parsing multipart/form-data if the boundary contained + signs.
* `Input`: Fixed a bug that could assign rubbish data to the `put()`, `patch()` or `delete()` data fields.
* `Input`: URI parsing has been improved to fix issues with URI's containing encoded data.
* `Input`: Incorrectly parsing multipart/form-data when the form boundary string contains + signs.
* `Pagination`: Fixed bug which allowed page numbers not to be numeric. This is now enforced.
* `Session`: Re-initialize if a session is started after it was closed (1.8.1.2 hotfix).
* `Unzip`: Addressed the zip-slip security vulnerability (1.8.1.6 hotfix).

### Packages

* `Email`: Mailgun driver has been made compatible with the Mailgun v3 API.
* `Email`: It is now possible to define stream socket options for SMTP connections.
* `Email`: A bug that failed to strip HTML comments from HTML email bodies correctly has been fixed. 
* `Oil`: Fixed broken SQL being generated for tables with Unique indexes in migrations.
* `Oil`: Fixed pagination when generating admin pages for tables.
* `Oil`: Admin scaffolding has been updated to generate better code.
* `Oil`: Everything related to Fuel Cells have been removed, it was never properly implemented. 
* `ORM`: A few PHP warnings have been fixed when calling `to_array()` on an object with relations.
* `ORM`: `to_array()` now returns related data correctly.
* `ORM`: `Observer_Typing` no longer truncates float values with more than 6 digits precision.
* `ORM`: `Observer_Typing` now supports columns of type 'date', 'time' and 'datetime'.
* `ORM`: Models now have the option to allow PK's to be set. This is required for non-autoincrement PK's.
* `ORM`: Fixed a NestedSets bug that caused the tree-id not to be set on multi-tree models.
* `ORM`: Fixed bug that allowed Models derived from a database view to be updated (causing a DB error).
* `ORM`: Allow `is_changed()` to run observers before comparing, to prevent it always returning True when using the Typing observer bidirectionally.
* `ORM`: Fixed bug in which not all Model properties were initialized on a `forge()` with partial data.

## v1.8.1

### Security advisories

* [**SEC-CORE-008**]:  Crypt encryption has been compromised!

See [the website](https://fuelphp.com/security-advisories) for more information about reported security issues and their status.

### Important fixes, changes, notes. Read them carefully.

* The code has been scanned for new warnings emitted by PHP 7.1.
* Support for PHPUnit v6 has been added.
* Support for php-fpm has been improved.
* Function overloading for multibyte functions is no longer supported.
* A workaround for PHP bug 55701 has been added.

### Security related

The AES encryption used by the `Crypt` class has been compromised, as reported by Felix Widemann and Nils Rokita from Hamburg University. They have proven that with a powerful GPU, any encoded string can be decoded using brute force in a few minutes. If your application relies on the `Crypt` class (and most do, because the session cookie is using `Crypt` to encode it), upgrading your applications is highly advised!

If you manually want to convert data, for example because you have them stored in the database, simply use:
````
$new = \Crypt::encode(\Crypt::decode($old));
````
It will detect if the string is using the old encryption or the new encryption. Your `crypt.php` containing the keys will be automatically updated as well (assuming the application has write rights to the file).

**Please note** that due to the stronger encryption mechanism used, the encrypted strings are longer. This might be an issue where you have limited space available, for example is fixed or max width database fields, a session cookie that is already approaching the 4Kb limit, etc. So check the requirements of your application before upgrading!

### Backward compatibility notes

* When you post a form that exceeds `max_input_vars`, in some PHP 7.x versions the excess values were silently dropped, causing incorrect application behavior. `Input` now emits an E_WARNING if PHP doesn't do so.
* The `Session` classes have been refactored. The methods `create()`/`read()` and `write()` are removed, and `start()` and `close()` added to more closely mimic native session behavior.
* Database results can now be returned in list or collection (cached) form. A list can only be iterated over, a collection has direct (array) access. By default a collection is returned to retain BC with 1.8.0, but in most cases, a list is faster if random access isn't needed, especially if the resultset is big.
* Function overloading for multibyte functions is no longer supported. When you have this enabled in your PHP config, Fuel will refuse to start.
* If you require multibyte agnostic string functions for the functions of type 2 (see http://php.net/manual/en/mbstring.overload.php), use the methods in the `Str` class instead.

### System changes

* Markdown has been updated to v1.7.0.
* Monolog has been updated to v1.18  (latest composer version).
* PHPSecLib has been updated to v2.* (latest composer version).
* URI parsing has been refactored for better NGINX and php-fpm support.
* The autoloader has been patched to better support classnames in local charactersets.

### Specific classes

* `Asset`: You can now call custom defined asset types the same way as you would built-in types (js,css,img).
* `Config`: `load()` has been refactored. It no longer overwrites on subsequent loads unless you want to. It also no longer returns `false` in that case, but always returns the loaded config.
* `Crypt`: Has been rewritten using Sodium. Decrypting old encoded strings is transparent, and will be converted on encrypting.
* `Date`: `create_from_string()` no longer allows you to create timestamps from before the Unix Epoch, which wasn't supported, and caused weird things to happen...
* `DB`: you can now use `on_open()` and `on_close()` when creating JOIN clauses.
* `DB`: UPDATE now supports the same JOIN clauses as SELECT.
* `DB`: Database result objects are now sanitized automatically when passed to a View.
* `DB`: You can now choose to return database results as a list (can only be looped over in sequence) or a collection (has random access). A list uses a lot less memory with large resultsets.
* `DB`: Introduced a `cache()` method to return a list as a collection.
* `DBUtil`: Now has a `list_indexes()` method.
* `Fieldset`: Fixed invalid HTML for tabular forms being generated when it contained hidden columns.
* `Fieldset`: Tabular forms now have built-in support for pagination.
* `File`: Fixed several bugs that could cause errors when `open_basedir` was in effect.
* `File`: Fixed broken file locking when using `open_file()`. Lock type validation added.
* `Form`: Attribute usage with both configured attributes and passed attributes on `open()` calls has been fixed.
* `Format`: Fixed a bug in which importing a multi-line CSV file could cause data loss.
* `Image`: Alphablending has been fixed for Imagick.
* `Image`: The Imagick driver now takes EXIF autorotation data into account, mimicing GD behaviour.
* `Input`: Only parses raw input when PHP hasn't done so (p.e. on put, patch or delete requests).
* `Input`: A new `raw()` method has been introduced to access the raw PHP input data (from php://input).
* `Log`: Error and Exception objects are now passed on to Monolog for more detailing logging options.
* `Model_Crud`: `count()` now uses the defined database connection, if available.
* `Model_Crud`: Freezing/unfreezing error fixed when unserializing data into an object.
* `Module`: You can now configure that you want routes loaded from the module when you load a module.
* `Pagination`: You can now specify the starting page (number, or 'first' or 'last') when no page number is present in the URL.
* `Security`: You can now configure NOT to rotate the CSRF token after validation.
* `Security`: `set_token()` is now a public method, so a token can be rotated manually.
* `Session`: Broken `destroy()` method has been fixed.
* `Session`: You can now create a session instance without implicitly starting it.
* `Session`: You can now reset an active session to an empty state.
* `View`: Fixed unsanitizing of Traversable objects.

### Packages

* `Auth`: Fixed a bug in the validation rules of the User model.
* `Auth`: When checking for access, you can now also pass the area name only (matches any right assigned in that area).
* `Auth`: For security reasons, OpAuths response has been changed from serialized to jsob. This response is now supported.
* `Oil`: Improved Model and Migration generation.
* `Oil`: Improved index support when generating migrations from an existing database table.
* `Oil`: Generated controllers now support pagination on their index page.
* `Oil`: Generating from existing tables now yield more details about the column.
* `Parser`: Markdown views no longer uses a dedicated version of Markdown, but the version installed via Composer.
* `Parser`: Creating a parser view object without a view name passed no longer triggers an exception.
* `Parser`: Support added for Handlebars templates though the LightnCandy composerpackage.
* `Orm`: `forge()` now accepts an object implementing ArrayAccess to add data to the ORM object.
* `Orm`: `Observer_Typing` now supports the fieldtype `encrypt` to transparently encrypt/decrypt data going into the database.
* `Orm`: `Observer_Typing` now support a field definition 'db_decimals', which you can use if your internal representation is different from the column definition (so objects aren't marked as changed incorrectly).
* `Orm`: Added a 'caching' config key to the ORM config, to configure default object caching behaviour.
* `Orm`: Now has a `caching()` method to enable or disable ORM object caching.
* `Orm`: Now has a `flush_cache()` method to flush the loaded ORM object cache.
* `Orm`: You can now disconnect related objects by assigning `null` or `array()` to the relation, which behaves identical to using `unset()`.
* `Email`: Mailgun email header generation has been improved.

## v1.8.0

### Important fixes, changes, notes. Read them carefully.

This version provides full compatibility with PHP 7. To achieve this, the \Fuel\Error class had to be renamed to \Fuel\Errorhandler. The new error handler has full support for PHP 7's new Error exceptions. If your application calls the Error class directly, or has extended the Error class, make sure you make the appropriate changes after you have upgraded!

The oil installer has been updated to use composer to install Fuel, and to provide better support for MacOS.

### Backward compatibility notes

* The included PHPSecLib version has been swapped by the composer package. If your application creates instances of PHPSecLib classes, check your code for compatibility issues, for example with the use of namespaces.

### Removed code (because it was deprecated in v1.7.3 or earlier)

* The old "mysql" DB driver has been removed because of removal in recent PHP versions. You can keep using `mysqli` if for some reason you don't want to use PDO. A new "mysql" driver has been introduced that uses PDO underneath. This should be transparent for most applications.

### Security related

* Because of the swap to the composer PHPSecLib package, the `pbkdf2()` method that was added to the code by the Fuel team is no longer available. Fuel itself now uses the PHP `hash_pbkdf2()` function. If you are using a PHP version < 5.5.0, this function is emulated in base.php.
* When using file based session, an additional check has been added to make sure the session file is loaded from the configured path.
* The `Security::clean_input()` now has support for `ArrayAccess` and `Traversable` classes, and now fully recurses into these classes and arrays for a full deep clean.
* `Security::generate_token()` now uses `random_bytes()`, `openssl_random_pseudo_bytes()` if available, and uses `hash_algos()` with SHA to generate the token hash.

### System changes

* The database classes have been refactored. `Database_Query` is now properly extendable, and `DBUtil` schema manipulations have been abstracted in order to support multiple DB platforms.
* New drivers have been added for "dblib" (MS-SQL/Sybase), "sqlsrv" (MS-SQL on Windows) and "SQLite".
* The framework now supports generic HTTP status 400 messages through the new `HttpBadRequestException` exception.
* When a database migration is run, and the database schema is ahead of the migration configuration file, the status is synced before any migrations are run. This makes sure migrations don't run twice, which may happen when you update multiple application instances using a shared clustered database.
* You can now correctly use "hybrid" controller (like "\Controller\Something_Class") names as documented.
* new function `get_composer()` allows direct access to the Composer Autoloader instance.
* The core's "base.php" code has been optimized for PHP 5.6+.
* A new route keyword ":everything" has been added, which complements ":any" by also matching with "nothing".

### Specific classes

* The Agent class now supports the definition of an HTTP proxy for downloading the browscap file.
* An issue in the Agent class is fixed where loading the browscap file could fail if it was defined as type "local".
* `Arr::key_exists()` now has support for classes implementing `ArrayAccess`.
* Asset now has a new method `add_type()` to define new types besides "js", "css" and "img". You need to pass a closure that is used to render the HTML for the given type.
* Asset is now more compatible with Windows platforms when it comes to generating paths and URL's (correct use of slash vs backslash).
* The Cache file driver has been improved to solve some locking race conditions.
* Config class no longer caches the result of config keys defined as a closure. Closures are now evaluated at runtime instead of at load time.
* Fixed a bug in `Controller_Rest` that would cause the HTTP status code to be overwritten when detecting an incorrect return format in production mode.
* `Controller_Rest` now returns HTTP status 200 by default.
* When calling the `Database` method `count_last_query()`, any ORDER BY is now stripped from the COUNT query to improve performance.
* Return type detection has been improved for `DB::query()` calls, for SQL statements like "DESCRIBE", "EXECUTE", "EXPLAIN" and "SHOW".
* `Date::range_to_array()` could return unexpected values when using more complex intervals. This has been fixed.
* Added support for "runtime-created functions" in Debug detailed output.
* Fixed a bug in `File::create_dir()` that causes directory creation to fail if the directory had the same name as its parent.
* `Form::open()` can now automatically add a CSRF key field when the config key "security.csrf_auto_token" is set.
* Lang now has a new method `set_lang()`, which allows you to switch the active language, optionally reloading all already loaded language files in this new language.
* Migrate can now detect circular dependencies (two migrations depending on each other), and will now bail out with a loop detection error message.
* Migration tasks can now define `before()` and `after()` methods. If either returns false, the migration is skipped. In case of `after()`, that implies the migration is reverted.
* Mongo_Db has a new `dump()` method to allow dumping a collection or collections for backup purposes.
* The `Response` class now has a new `set_headers()` method to set multiple headers in one go.
* `Request_Curl` now returns the complete "raw" response in the response variable "response", which can be accessed in case of a returned http status >= 400.
* `Request_Curl` now allows you to use fully qualified option names to be set (those starting with "CURLOPT_").
* The Router now also returns the path of the controller on a found route match.
* The Security class can now throw an `HttpBadRequestException` instead of a generic `Security Exception` when CSRF validation fails.
* The Session file driver has been improved to solve some locking race conditions, and an additional validation of the session payload on session load.
* The "randomness" of generated session ID's has been improved by using `Security::generate_token()` to generate them.
* The Str class now checks if mbstring functions are available before using them.
* Theme is now more compatible with Windows platforms when it comes to generating paths and URL's (correct use of slash vs backslash).
* Validation `valid_date` rule can now handle incomplete date/time formats properly by using defaults for missing values.
* You can now control the behaviour of the View class on closures assigned to a View variable through the config key "filter_closures".
* For View variables supporting the `Sanitation` interface, sanitation is disabled after rendering the view to return the object in its original state.

### Packages

* Auth: login drivers now uses the internal PHP function `hash_pbkdf2()` function to hash passwords.
* Auth: the Opauth driver will now pass a "group_id" back in the result if the login provider supplies this value in its response.
* Auth: the Opauth driver now has a `get_instance()` method to return the current Opauth instance.
* Auth: migrations now use the configured "db_connection" from the simpleauth/ormauth config, if defined.
* Auth: `auth_check()` now also accepts the name of the login driver (as a string), besides the login driver instance.
* Auth: updated the Auth classes to support the PHPSecLib composer package.
* Email: Fixed bug in text wrapping where spaces could be stripped from HTML tags.
* Email: Added a check on the availability of mbstring extensions before its functions are used.
* Oil: migrate now has a new "--installed" option, which only runs migrations for packages and modules defined in the "always_load" section of the config. You can use it in conjunction with "--modules" and "--packages" to include some manually loaded modules or packages.
* Oil: new "--with-test" option to scaffolding will generate corresponding test classes for each class generated.
* Oil: "fromdb" task has a new "migration" command that allows you to generate migrations from an existing database. NOTE: these need to be checked as not all details can be retrieved from an existing table!
* Orm: Fixed SQL generation error when `DB::expression()` was used at the left-hand side of a query statement.
* Orm: Fixed a decimal point positioning issue in the Typing observer.
* Orm: Fixed a problem in the Temporal model where the incorrect primary key values where used when generating a WHERE clause.
* Orm: Added the option to `dump_tree()` to include a path URI, mainly useful when working with nested sets.
* Orm: The typing observer can now handle floats in all locales (the decimal point is comma problem).
* Orm: Implemented a workaround for slow access of large array entries by reference (see https://bugs.php.net/bug.php?id=68624)
* Orm: A bug that caused related data in a many-many relation to be incorrectly hydrated has been fixed.
* Parser: It is now possible to load Mustache partials. If none are defined, the UTF-8 partial is loaded by default.
* Parser: for View variables supporting the `Sanitation` interface, sanitation is disabled after rendering the view to return the object in its original state.
* Parser: for jade templates, now the Talesoft Jade renderer is supported too, besides the already supported Everzet renderer.
* Parser: fixed a bug that caused loading template files with multiple dots to fail.
* Parser: Twig templates now have access to the `Auth::get()` method through "auth_get".

## v1.7.3

### Important fixes, changes, notes. Read them carefully.

This release is mainly a bugfix release, although some minor functionality was added as well. The main goal of this release is increased stability of the version 1 code, now that it is approaching the end of it's life-cycle.

The final version will be v1.8, which will be released at the same time the first version of Fuel v2 will be released. It will be an LTS version, no new functionality will be accepted on the codebase anymore, but will will keep releasing bugfixes and security fixes.

### Backward compatibility notes

* The FuelPHP framework is now entirely loaded using composer. A check has been added to the frontloader to make sure composer has run and all components are installed, and die with a proper error message if that is not the case. The default `minimum-stability` is set to `stable`, so you might want to have a look at that if needed.
* Activating the framework autoloader has been moved from the App bootstrap to the frontloader (oil for cli, public/index.php for web requests). When you upgrade, make sure to update both the frontloaders, otherwise you will get exceptions when the autoloader is loaded twice.
* When loading multiple modules or packages through the `load()` method, the result will now only be `true` if all could be succesfully loaded.
* When Fuel is run is CLI mode, output buffering is now disabled. Note that it might still buffer, for example because you have enabled buffering globally in your php.ini.
* The `match_collection` validation rule now always returns `true` if no collection was passed to match against.

### Removed code (because it was deprecated in v1.6 or earlier)

n/a

### Security related

* PHPSecLib has been updated to a more recent version.
* Htmlawed has been updated to version 1.1.19.

### System changes

* The dependency with `FuelPHP\Upload` is now with version 2.0.2.
* The frontloader now has a generic Exception catching mechanism. For every Exception caught you can have the frontloader route to a route entry of your choice. By default, these are defined: `HttpNotFoundException (_404_ route)`, `HttpNoAccessException (_403_ route)` and the `HttpServerErrorException (_500_ route)`.
* The finder caching system has been updated to avoid incorrect cache hits when loading files from modules or packages.
* Module and package paths are now forced to be lowercase to comply with the standards.
* You can now configure additional paths to be cleaned, to avoid giving away FQFN in error messages.
* When running migrations, your `up()` or `down()` method can return false to signal it can't execute the method at that point in time. Migrations that use this method are now automatically re-tried in a second migration run. This helps with dependencies, to make sure migrations run in the correct sequence (for example if an app migration requires a package migration to run first because it needs access to its tables).

### Specific classes

* __Cache__: The XCache `delete_all()` method now actually deletes it all.
* __Config__: when using a database as backend storage, you can now specify the name of the database config that needs to be used to access the "config" table.
* __Config__: you can now store config information in a memcached backend (think about persistency!).
* __Controller_Hybrid__: calling a REST method from a browser now returns the correct result.
* __Crypt__: can now be instantiated, if you need to use multiple crypt keysets in your application.
* __DB__: `count_last_query()` now correctly handles SQL containing sub-queries.
* __DB__: database result objects can now be assigned a custom sanitation for specific encoding/decoding logic when results are send to a View. This also means you will no longer get a "database results are read-only" exception when you do.
* __Error__: the log level used for errors is now configurable.
* __Form__: the `label()` method now has support for the "for" attribute.
* __Format__: new parameter for `to_xml()` allows you to specify how booleans must be represented (0/1 vs false/true).
* __Inflector__: the inflector ruleset has been moved to a lang file, so it can easily be
amended, and provide support for introducting non-english language rulesets.
* __Input__: new `query_string()` method to return the main requests query string.
* __Input__: a header value lookup is now done in a case-insensitive manner.
* __Lang__: now allows you to load the same lang filename for different languages concurrently.
* __Lang__: `load()` now has support for dot-notation when loading into an existing group.
* __Log__: Monolog initialisation has moved to a separate method, making it easier to overload it.
* __Migrate__: will now autoload a module or a package before it executes its migrations.
* __Module__: when unloading a module, the routes defined by the module will be correctly removed.
* __Pagination__: page and item calculations have been improved, to allow more flexibility in passing page data to the object.
* __Presenter__: now supports the "::" notation to force loading a presenter from a module.
* __Response__: now has loop detection for `redirect_back()`.
* __Session__: driver garbage collection has moved to a separate method, making it easier to overload it.
* __Session__: the `rotation_time` configuration key can now be set to false to completely disable automatic session id rotation. Use with care!
* __Theme__: you can now specify the other in which partials must be rendered for output. This allows you to render content before headers and footers, needed to dynamically add assets.
* __Theme__: the `presenter()` method now allows you to pass a custom view name (like `Presenter::forge()` that is theme aware.
__Validation__: the `match_collection` rule can now be run in `strict` mode, which meanly helps when validating booleans.
* __Validation__: new rule `specials` allows matching against non-latin characters considered alphabetic in unicode.
* __View__: `get()` and `set()` now supports dot-notation for getting values from stored arrays.

### Packages

* __Auth__: the broken support for separate read- and write DB connections has been fixed.
* __Auth__: Ormauth now correctly handles uses without any group.
* __Auth__: Ormauth now keeps the current users effective rights in memory for faster access.
* __Auth__: the use of `force_login()` now correctly registers the drivers logged-in state, so a global logout will now do what it promises.
* __Auth__: drivers now force a session id rotation on login.
* __Auth__: when using "Opauth", related provider records are now deleted when the user is deleted.
* __Auth__: Orm models now correctly define their properties, to allow overloading.
* __Auth__: `login()` now has multi-driver support (will attempt to login all drivers if configured)
* __Auth__: the `opauth` interface class now also supports Opauth packages that are not HTTP based (like [this LDAP driver](https://github.com/FlexCoders/opauth-ldap)).
* __Email__: header encoding is now disabled for the "Mandrill" driver.
* __Email__: the "Mailgun" driver now has support for attachments.
* __Oil__: generated templates can now handle custom Auth drivers, as long as they extend one of the included drivers.
* __Oil__: the PHP server command has been fixed for use on Windows platforms.
* __Orm__: The slug observer now also works for Model_Soft models.
* __Orm__: `to_array()` now handles multi-level relations of different type a lot better.
* __Orm__: in a "many_many" relation, you can now define an ordering on a column in the "through" table.
* __Orm__: models now allow you to define separate read- and write database connections.
* __Orm__: better support for select(). Please not that is it still not advised to use this, and it is still required to have the PK as part of the result.
* __Orm__: the behaviours `filter_properties` and `array_excludes` for `to-array()` now have getters.
* __Orm__: `where()` now accepts a single DB::expr() object as argument.
* __Orm__: `set()` now allows you to pass an array structure that can recursively set relations (currently "has_one" and "belong_to" only).
* __Parser__: now also handles view files with a ".php" extension correctly.
* __Parser__: you can now use `Debug::dump()` in a twig template.
* __Parser__: in Twig templates you can now access the current Asset instance to load css, js or image files.

## v1.7.2

### Important fixes, changes, notes. Read them carefully.

##### Viewmodel

As of 1.7.2, the Viewmodel class is deprecated, and replaced by the Presenter class. Functionality has remained largely the same, and a Viewmodel alias is present to maintain backward compatibility.

It has proven difficult to explain what a Viewmodel is and does, and why you should use it. Also having a classes/view and a views folder was very confusing for a lot of people. It is also a step closer to Fuel v2, there this class is also called Presenter.

### Backward compatibility notes

##### Request_Curl

As a result of the security issue mentioned below, the auto-format of the response in the `Request_Curl` class is now disabled by default, as it is possible for a malicious site to construct a response of a specific reponse type that can lead to code execution. This means that if you use `Request_Curl`, you have to either enable this manually in your code (**ONLY** if you absolutely trust the site you connect to!), or add code to validate the response before you process it.

##### Validation

The validation rule `required` rule no longer treats an input value `false` as a value, so passing this value will now trigger a validation error.

##### Database

The PDO driver now returns the error code of the underlying database driver back as the error code in the `Database_Exception`, instead of the PDO error code. This allows you to act on specific platform errors.

It also means you loose access to the original generic PDO error code, which you can work around by retrieving the current PDO database connection (through the `connection()` method on the database object) and call PDO's `errorCode()` method to retrieve the original generic PDO error code.

### Removed code (because it was deprecated in v1.7.1 or earlier)

None.

### Security related

##### Request_Curl

There was one security advisory issued for 1.7.1, which also impact all previous versions from 1.1 onwards (see http://fuelphp.com/security-advisories). The issue is mitigated in 1.7.2, it is strongly advised that you upgrade as soon as possible, or alternatively follow the advice in the advisory.

##### Database

A potentional vulnerability was discovered in the way column name quoting was done. This has been fixed. This means that coding SQL functions manually was something you could get away with earlier now require you to use DB::expr() to encapsulate the function.
````php
// old code, no longer works
$result = DB::select("LOWER \"field\")")->from($table)->execute();

// has to be replaced by
$result = DB::select(DB::Expr("LOWER \"field\")"))->from($table)->execute();
````

##### Errors

Error messages are now escaped, to prevent a possible XSS through the generated error. Note that it is best practice not to display error messages in a production environment, so the possible risk for XSS is deemed to be very low.

### System changes

* A possible XSS vulnerabity in the Profiler output has been fixed.
* The `import()` function can now also import third-party classes in APPPATH/vendor.
* When using multiple DB connections, the profiler now shows the connection used for the query.
* The Profiler now html encodes the output to avoid incorrect handling of the ampersand.
* The internal Markdown class has been removed, and replaced by the Composer library.
* Some methods were still defined as `private`. This has been changed to `protected` to allow extension.
* Fully namespaced controllers are now supported. Now you can use class names like Controller\Foo\Bar, Controller_Foo_Bar, or Controller\Foo_Bar.
* The Database layer now has support for nested transactions, either through native SQL support, or via SAVEPOINTS.
* The __Agent__ class has been switched back to the original browscap.org URL's.
* Saving a __Lang__ or __Config__ file will now flush the APC and/or Opcode cache.
* Double quotes inside an HTML tag attribute value are now escaped.
* Debug logging has been added to the Session classes to aid in debugging session loss.
* GZIP compression is now automatically disabled if the client indicates it doesn't support it.
* Unit tests have been adapted where needed to support PHPUnit 4.
* The `html_tag` helper function now generates compliant HTML.
* Several pieces of file handling code has been modified to handle Windows file paths better.
* The Autoloader now thows an exception if the class file can be found, but it doesn't contain the class expected.
* The Autoloader now supports loading Traits.
* The shutdown handler now logs any error if it fails to shutdown properly.
* The included PHPSecLib version has been upgraded to the July 1st version of the php5 branch.
* Unit testing now has support for AspectMock.
* An entry to the phpunit xml has been added to run tests in modules.

### Specific classes

* __Agent__: Now correctly uses the defined browser agent instead of the system one.
* __Arr__: New `keyval_to_assoc()` method converts key-value pairs into an associative array.
* __Arr__: When passing an object as key to `get()`, it is now cast to string.
* __Asset__: Has a new config option "always_resolve", which will do local asset resolving even for absolute URL's.
* __Cache__: A check is added to avoid possible deadlocks with using files for caching.
* __Cache__: Now has a driver for Xcache (http://xcache.lighttpd.net).
* __Cli__: Backtrace output has been rewritten to make it more readable on the commandline.
* __Cli__: Now has the option to disable output colouring.
* __Cli__: New methods `stdout` and `stderr` allow you to redirect them to file.
* __Config__: When saving a config file, the configured permission mask is now applied.
* __Controller_Hybrid__: Now correctly handles returned array responses.
* __Controller_Rest__: When no data is returned, "204 NO CONTENT" status is set.
* __Controller_Rest__: Better support for Digest authentication.
* __Controller_Rest__: The option to specify the return format in the URL now actually works.
* __Database__: The PDO driver will now add the `charset` to the DSN if not specified.
* __Database__: The MySQL drivers no longer use the "AUTOCOMMIT" value, which interferes with table locking.
* __Database__: `Insert` now has the options to define multiple value sets, to insert multiple rows at once.
* __Database__: Quoted strings can now passed to methods without requiring `DB::expr()`.
* __DBUtil__: Default values are now correctly quoted, instead of escaped.
* __DBUtil__: Make sure the `COMMENT` keyword appears before `AFTER` and `BEFORE`.
* __DBUtil__: Now allows you to set a specific DB connection to operate on.
* __Fieldset__: `field()` without parameter will now correctly return all defined fieldset fields.
* __Fieldset__: Now has a `delete()` method to remove an existing field from the fieldset.
* __Fieldset_Field__: `set_fieldset()` now allows you to move a Field to a different fieldset.
* __File__: `download()` now has the option to delete the file after download is completed.
* __File__: New `file_exists()` method that will honour the defined Area.
* __File__: Fixed possible infinite recursion in `delete_dir()`.
* __File__: `Download` now supports the option to select "inline" or "attachment" disposition.
* __Form__: If no action is specified to `open()`, the current URI will be used.
* __Format__: CSV conversion methods now have separate config for import and export of CSV data.
* __Format__: Improved CSV parsing, to support non-standards formats created by Microsoft applications.
* __Format__: CSV files without headers can now be imported.
* __Format__: `to_csv()` now allows you to define custom headers.
* __Format__: Incorrect handling of empty XML tags has been fixed.
* __FTP__: Fixed directory recursion in `delete_dir()`.
* __Inflector__: The separator of `friendly_title()` is now configurable.
* __Input__: Added better support for NGINX.
* __Lang__: If multiple languages are defined, the lang files are now loaded in the correct order.
* __Log__: New `log_filename` config key allows you to override the generated log file name.
* __Model_Crud__: Can now correctly handle properties with a `null` value.
* __Mongo_Db__: `like()` method now correctly uses it's wildcard parameters.
* __Mongo_Db__: New method `list_collections()`.
* __Pagination__: Logic has been completely rewritten to fix all bugs.
* __Pagination__: You can now define an offset to shift the active page in the navigation block left or right.
* __Session__: `set_flash()` now correctly resets the state when setting an existing expired flash value.
* __Session__: Deleting a session cookie now takes the configured path and domain into account.
* __Session__: Now has emulation of $_SESSION, to support external code using this to access session data.
* __Str__: The `truncate` method now correctly handles multibyte strings.
* __Uri__: An empty URI string is valid input for `Uri::create()`.
* __Uri__: Uri suffixing has been rewritten to accept new long TLD names.
* __Validation__: `valid_string` now allows you to test for "slash" and "backslash".
* __Validation__: New `valid_collection` rule allows you to check against a predefined list of values.
* __Validation__: The `required` rule no longer treats `false` as a value.
* __Viewmodel__: Now has the option to unset a variable set on it.

### Packages

* __Auth__: The included ORM User model now supports both Ormauth and Simpleauth.
* __Auth__: Ormgroup's member() method now correctly checks for group membership.
* __Auth__: Calculating effective user permissions in Ormauth has been fixed.
* __Auth__: `Opauth`: if no nickname is returned by the provider, try to find a match on email address.
* __Auth__: Ormauth now supports database selection and DB's replication features.
* __Email__: Recepient names are now quoted to support comma's in the name.
* __Email__: New `Mailgun` driver to support sending email through Mailgun's email service.
* __Email__: SMTP driver now only authenicates ones per connection, to facilitate bulk email.
* __Email__: Added the option to strip or leave HTML comments in the HTML message body.
* __Email__: New `Mandrill` driver to support sending email through Mandrill's email service.
* __Email__: SMTP driver now supports STARTTLS for secure email. Used for example by Google mail.
* __Email__: Now has a config option to automatically correct relative protocol URI's in HTML bodies.
* __Oil__: `oil server` now has inline help.
* __Oil__: Authentication in generated Admin controllers has been fixed.
* __Oil__: Added the `--module` argument to add module support to the code generation commands.
* __Oil__: Scaffolding templates are updated for Boostrap 3.
* __Oil__: `oil test` now supports the PHPunit argument `--testsuite`.
* __Oil__: `oil test` now supports the PHPunit argument `--debug`.
* __Oil__: `oil generate model` now can generate ORM temporal or nestedset models.
* __Oil__: the `--with-viewmodel` switch has been renamed to `--with-presenter`.
* __Oil__: when `refine` calls an unknown command, it now lists the ones defined in the Task.
* __Oil__: Generated view code now works properly cross platform and cross OS.
* __Orm__: `to_array` now also also exports EAV value pairs.
* __Orm__: The `before_save` observer is now called before the object is checked for changes.
* __Orm__: New `enable_event` and `disable_event` methods for enabling/disabling observer events.
* __Orm__: Fixed `Soft_Delete::purge`, now it actually deletes the purged records.
* __Orm__: You can now pass custom data when forging an ORM object.
* __Orm__: Observer_Slug now has a configurable separator.
* __Orm__: Observer_Slug now allows you to generate duplicate slugs, or to assign slugs manually.
* __Orm__: Fixed validation of new objects, now all fields are correctly validated.
* __Orm__: There is now support for `DB::expr()` in ORM `select()`.
* __Orm__: There is now support for `select('*')`.
* __Orm__: You can now add custom sanitation code to a model (used when a model object is passed to a View).
* __Parser__: Twig driver now support `Input::post`, `Session::get` and `Auth::check`.
* __Parser__: Smarty driver how has the same Fuel interface plugins as Twig.
* __Parser__: Now supports the "Lex" parser (http://github.com/pyrocms/lex).

## v1.7.1

### Important fixes, changes, notes. Read them carefully.

* The index.php has been updated to make sure the Response body is rendered, and rendered only once. When upgrading to 1.7.1, **don't forget** to apply these changes!
* When using the REST controller and returning an array as a response, the controller now checks if the response format is compatible. If not, it will return an error messsage and set a 406 HTTP status when in production mode. In other modes, it will return a warning and a JSON encoded dump of the array.

### Backward compatibility notes

None.

### Removed code (because it was deprecated in v1.7 or earlier)

None.

### Security related

There were one security advisory issued for 1.7, which also impact all previous versions (see http://fuelphp.com/security-advisories). These issues are addressed in 1.7.1, it is strongly advised that you upgrade as soon as possible.

### System changes

A new **Sanitization** interface has been introduced to the core. Objects can implement this interface, and when you pass such an object to a View, the object will not be cleaned, but the individual properties will be cleaned by the object itself when the properties are requested by the view.

ORM and Model_Crud models now implement the sanitization interface by default, and when enabled, they will return a cleaned copy of the property, instead of the property itself. This means you can now pass ORM model objects to Views, without the ORM object being destroyed.

### Specific classes

* __Cache_Storage_Redis__: Support for non-default Redis DB configs has been fixed.
* __Arr__: `get()` now allows you to get array values using a key that contains a dot.
* __Arr__: `search()` now has a new parameter to enforce a strict search.
* __Asset__: `css()` now accepts the 'type' attribute.
* __DB__: Now allows a DB connection to be set, to make sure SQL is compiled using the correct driver.
* __DB__: You can now `disconnect()` and `connect()`, allowing you to reconnect when the connection has dropped.
* __DBUtil__: `set_connection()` now accepts `null` to reset the connection instance set previously.
* __DBUtil__: `add_foreign_key()` now has support for custom DB connections.
* __DBUtil__: `create_index` now allows you to create a PRIMARY KEY index.
* __DBUtil__: `drop_index` now allows you to drop a PRIMARY KEY index.
* __File__: `download()` will now be executed after cookies have been written.
* __Form__: `select()` now accepts zero or null as selected value.
* __Format__: `from_xml()` now has support for XML namespaces.
* __Format__: `to_json()` now accepts JSON encoding options, with configured default options.
* __Fuel__: Make sure the locale is set before processing 'always_load'.
* __Image__: `create_hex_color()` now correctly processes the alpha value.
* __Image__: `convert_number()` can now deal properly with numbers using a decimal comma.
* __Image__: Imagemagick driver now correctly stores the image size in its cache.
* __Input__: `uri()` now always returns the URI with a leading slash.
* __Input__: You can now control double decoding of urlencoded forms.
* __Lang__: No longer uses a fixed path delimiter, causing issues on Windows.
* __Lang__: When multiple languages are defined, the files are now loaded in the correct order.
* __Migrate__: Now displays the correct migration version when migrating down.
* __Migrate__: Now checks for existence of packages and modules before attempting to migrate them.
* __Model_Crud__: Now implements lazy sanitation when an object is passed to a View.
* __Theme__: You can now call `render()` more than once.
* __Uri__: When $_GET is reassembled, it will now be security cleaned.

### Packages

* __Auth__: Problems with direct updates of permission join tables (PK=FK) have been fixed.
* __Auth__: The `multiple-logins` config setting is now ignored unless there actually are multiple login drivers.
* __Email__: A background color (#aabbcc) in an img tag is no longer seen as an attachment.
* __Parser__: `auth_has_access` has been added as a Twig function.
* __Oil__: Improved error reporting when it is unable to parse the given field definition.
* __Oil__: When running a module task, the module path is now added at the front of the finder path list.
* __Oil__: Duplicate migration filename detection has been fixed.
* __Oil__: Refine will no longer dump the callstack when an exception occurs in production mode.
* __Oil__: Generating a drop table migration has been fixed.
* __Orm__: Models now implement lazy sanitation when an object is passed to a View.
* __Orm__: Missing config for Temporal models has been fixed.
* __Orm__: Model_Temporal `find_revision()` no longer throws an exception when no revision could be found.
* __Orm__: The `UpdatedAt` observer now has the option to mark the object as updated if a related object was changed.
* __Orm__: The `Slug` observer now has the option to define a custom separator.

## v1.7

[Full List of core changes since 1.6.1](https://github.com/fuel/core/compare/1.6/master...1.7/master)

### Important fixes, changes, notes. Read them carefully.

* A fix has been added to deal with PHP bugs #42098/#54054, which cause an SPL autoloader to malfunction when trying to autoload from an exception handler. This will fix erradic "class not found" messages when processing exceptions.
* __File::close_file()__ was broken when using locking. This has been fixed.
* __Date::test_format()__ no longer resets the current timezone to UTC.
* Output buffering is now disabled when in CLI mode. This allows you to get messages from your tasks in realtime, instead of having to wait until the task has finished.
* A bug in all session drivers (except cookie) that caused the session timestamp not to be updated has been fixed. The session will now not expire as long as there is activity within the expiration timeout.
* PHP E_ERROR's are now reported as "Fatal error".
* Profiler data will no longer be added to the output if the request is an ajax call.
* The finder now checks for "?:\" to detect a Windows path, so that one-letter module names can be used in finder filenames ("?:filename").
* Where relevant calls to `file_exists()` have been changed to `is_file()` for performance reasons.
* Where relevant calls to `call_user_func_array()` have been changed to `call_fuel_func_array()`, our internal equivalent which is about 30% faster.
* Lots of bugfixes in the __Auth__ package, especially in relation to OpAuth and the Ormauth drivers.
* Lots of bugfixes in the __Orm__ package, especially related to Model_Soft and Model_Nestedset.

### Backward compatibility notes

* The CSV configuration for the Format class has been split into a separate config for imports and exports. Also, the default escape character has been changed from a backslash to a double quote, to be more standards compliant. If your application relies on the backslash, make sure to create a custom format config after you have upgraded.
* The __Redis__ class has been renamed to __Redis_Db__, to avoid collisions with the __Redis__ PECL class, which seems to be installed by default on a lot of systems. If the PECL extension is not found, __Redis_Db__ will be aliased to __Redis__, to make sure existing applications that use the __Redis__ class don't break. If you use the __Redis__ class, it is advised that you change it to __Redis_Db__.
* __Cookie::set()__ now returns `false` when called in CLI mode.

### Removed code (because it was deprecated in v1.6 or earlier)

* The __Event::shutdown()__ method has been removed. This is replaced by two events, 'shutdown' which can be used by applications to run code after the script has finished, and 'fuel-shutdown', which runs after the application shutdown events, will close any open session, and runs the framework cleanup.

### Security related

There were two security advisories issued for 1.6.1, which also impact all previous versions (see http://fuelphp.com/security-advisories). These issues are addressed in 1.7, it is strongly advised that you upgrade as soon as possible.

### System changes

* Composer now runs "oil refine install" when you run the initial installation.
* A `web.config` file is now included to support rewriting for those using PHP/IIS on Windows.
* Twitter Bootstrap has been upgraded to 3.0. Check for dependencies with your current code if you upgrade, oil will now generates 3.0 compliant view files.
* FuelPHP\Upload has been switched to version 2.0.1. If you upgrade, don't forget to change your composer.json and run `php composer.phar update`.
* Exception handling in the index.php now resets the main Request, to avoid subsequent Requests to be seen as HMVC calls.
* index.php now only updates the output with profiling data if the placeholders are present in the output.
* All code using preg_replace() with the \e modifier has been rewritten to be compliant with PHP 5.5+.
* Reverse routing now works with regex routes.

### Specific classes

* New __Arr::merge_assoc()__ method as alternative to array_merge_recursive(), which does not alter numeric keys, and does not merge mixed values (see docs).
* New __Arr::reindex()__ method to recursively reindex an indexed array, or the numeric keys in an assoc array.
* New __Arr::subset()__ method returns a subset of an array based on a list of (dot-notated) keys.
* __Asset__ methods `css()` and `js()` now allow inline code to be passed as a string.
* __Cache_Storage_Memcached__ now creates and reuses a single connection to the Memcached server.
* __Cache_Storage_Memcached__ now supports a relative expiration time, like the native PHP functions.
* __Cache_Storage_Redis__ now creates and reuses a single connection to the Redis server.
* __Config__ now has a driver to store config data in a database table.
* __Date__ has a new emulation function for strptime(), for better Windows support.
* __DB__ now supports master/slave configurations, completely transparent for the application.
* __DB__ where() method now has support for DB::expr().
* __DB__ configuration now has support for speciying the collating sequence.
* New `has_connection()` method for __DB::instance()__ to check if a valid DB connection is present.
* You can now create nameless __Fieldset__ objects.
* __File_Handler_File__ now has a new method `get_path()` to retrieve the file's path.
* New __Form::csrf()__ method to add a hidden field to your form with the CSRF token.
* __Form::select()__ now allows you to pass a default value via the attributes array.
* __Form::to_xml()__ now has a configuration option to escape data using CDATA instead of converting to HTML entities.
* __Fuel__ has improved base_url detection, to work better with installations inside the document root.
* New __Image__ `extension()` method to retrieve the extension of the loaded image.
* __Input__ now supports the HTTP method PATCH.
* __Lang__ now has a driver to store language data in a database table.
* __Log__ now tells you why it couldn't open or write to the logfile.
* __Migrate__ now prints a warning if one or more migration steps were skipped.
* __Mongo_Db__ now uses the MongoClient class, instead of the deprecated Mongo class.
* __Pagination__ now casts all numeric values to int after calculation.
* __Pagination__ now has a `__toString()` method to render when the object is cast to string.
* __Pagination__ render methods now correctly use the configured default values when called without arguments.
* __Pagination::render()__ now has the option to return the raw pagination data array instead of the rendered HTML.
* New config section for __Pagination__ to support Bootstrap v3.
* New __Redis_Db__ method `psubscribe()` allows you to listen and define a callback for every response.
* __Request__ now writes the request type to the log, together with the requested URI.
* __Request__ now checks if all required action arguments are present, and throws HttpNotFoundException if not.
* __Response__ now has an updated HTTP status code list (including the famous 418!).
* The use of wildcards in a URI for __Response::redirect()__ is now configurable.
* __Security::xss_clean()__ is now using htmLawed v1.1.16.
* New options parameter for __Security::xss_clean()__ to pass custom configuration to htmLawed.
* __Security::check_token()__ now uses Input::param() to fetch the token. This allows HTTP methods other then POST to be secured with a token.
* New __Str::random()__ feature to generate UUID v4 strings.
* __Uri::segment_replace()__ now allows you to force the URL scheme to HTTP or HTTPS.
* New __Uri::update_query_string()__ method allows you to add query string data to an existing URL.
* __Validation::valid_date()__ will now ensure the date value format is valid.
* New __Validation::get_error_message()__ to retrieve all or individual error message from validation error objects. This saves you having to loop over the objects in your code to get the messages out.
* __Validation::valid_string()__ now has support for "brackets" and "braces".
* __Viewmodel::forge()__ now allows you to pass a custom view name or View object.
* New __Theme::viewmodel()__ method allows you to create theme aware Viewmodel objects.
* __Upload__ init method has been modified to make sure uploaded files are not processed twice.
* New __Uri::build_query_string()__ method to generate a query string from a list of arrays or strings.

### Packages

* __Auth__: Opauth driver now has a config switch to allow auto registration after OAuth login.
* __Email__: Added support for images in base64 encoding.
* __Email__: Fixed additional blank line in the mail header causing some mailservers to barf...
* __Email__: New getter methods `get_from()`, `get_to()`, `get_cc()`, `get_bcc()`, `get_subject()`, `get_body()` and `get_reply_to()`.
* __Oil__: There is now support for the `_init()` static method for tasks.
* __Oil__: `test` now has (expiremental) support for __phpunit.phar__.
* __Oil__: Scaffolding menu links are now rendered as buttons.
* __Oil__: Running `create` inside a valid FuelPHP installation now displays an error message.
* __Oil__: Generating a migration without the correct arguments now fails with an error message.
* __Oil__: Scaffolding and Admin scaffolding now generate Bootstrap v3 compliant view files.
* __Oil__: New `package` command generates a skeleton for a new package.
* __Oil__: You can now use dashes and underscore in generate field options (p.e. enum values).
* __Oil__: All commands now have a help screen.
* __Orm__: __Observer_Typing__ now creates an empty array when calling unserialize() on a NULL column value.
* __Orm__: `get_one()` now uses `rows_limit(1)` when fetching an object with related objects.
* __Orm__: New `Model_Soft::purge()` method allows bypassing the soft-delete functionality and delete an object permanently.
* __Orm__: `Model::to_object()` can now be called with the same arguments as `to_array()`.
* __Orm__: `group_by()` Query method now supports relation name prefixes for column names.
* __Orm__: You can now pass additional conditions to a lazy get() of a related object.
* __Orm__: __Model_Soft__ now has support for `count()`, `min()` and `max()`.
* __Orm__: `min()` and `max()` results are __no longer__ cast to int, so they can be used on date columns.
* __Orm__: `Model::to_array()` has better object tracking to prevent recursion.
* __Orm__: `from_array()` now has support for the EAV extension. Importing non-model properties will now create EAV records instead of custom data if the model has an EAV container configured.
* __Orm__: There is now support for EAV containers in `Model_Nestedset`.

## v1.6.1

### Backward compability notes

__Orm__: You can no longer use property assignment to create a custom property on a model object if that model implements an EAV container. It will set an EAV value instead.

### Removed code (because it was deprecated in v1.6 or earlier)

__Orm__: calling `find()` with no parameters or with a single parameter that is `null` will return `null` as a result. It will no longer throw an exception.

### System changes

* Fixed broken CSS code in the welcome controller views.
* Improvement to the query analysis information displayed in the profiler (MySQL only).
* The included __Markdown__ library has been upgraded to v1.2.6.
* Lots of path processing improvement to have the framework work better on Windows.
* Finder now ignores the cache if there are permission issues on the cache file.

### Specific classes

* __Agent__: Don't try to fetch browser information if no user agent is present in the server data.
* __Arr__: New `previous_by_key()` method to fetch the previous key or value from an array using the current key.
* __Arr__: New `previous_by_value()` method to fetch the previous key or value from an array using the current value.
* __Arr__: New `next_by_key()` method to fetch the next key or value from an array using the current key.
* __Arr__: New `next_by_value()` method to fetch the next key or value from an array using the current value.
* __Cache__: `delete_all` in the File driver now properly recurses all folders.
* __Controller__: Now has a `response_status` property to set the HTTP status for automatically created responses.
* __Controller_Rest__: Fixed using a controller method to determine the authentication status.
* __Controller_Rest__: Fixed warning when the format passed in the URL is not a valid format.
* __Crypt__: Now uses the file permissions defined in the file.php configuration file when generating the crypt configuration file.
* __Format__: "to" methods now deal with a passed null value correctly.
* __Html__: Now enforces HTML5 by default.
* __Input__: New `headers` method allows you to fetch HTTP headers.
* __Input__: New `allow_x_headers` configuration key controls if using X-headers are acceptable.
* __Inflector__: Method `friendly_title()` now correctly deals with apostrophes by replacing them with a separator.
* __Request_Curl__: Added support for the 'HEAD' method to the cURL driver.
* __Response__: New `redirect_back()` method to redirect back to the previous page in your application.
* __Session__: Now supports passing the session id as a string in get/post variables.
* __Session__: Now supports passing the session id in the "Session-Id" HTTP header.
* __Session__: Now allows you to disable creating a session cookie if you want to pass it manually.
* __Session_Redis__ : Fixed recovering from expired sessions when using the Redis driver.
* __Theme__: Now allows you to store module theme views inside the module folder.
* __Theme__: New `partial_count` and `has_partials` methods.
* __Upload__: Implemented missing save() argument behavior, for backward compatibility.
* __Viewmodel__: New `get_view()` method returns the associated View object.

### Packages

* __Auth__: Now includes secure "remember_me" functionality.
* __Auth__: Fixed typo in the Simpleauth migation file, causing a missing 'group' column
* __Auth__: Ormauth's `create_user()` method now supports updating profile fields (which are mapped to EAV attributes) for compatibility with Simpleauth.
* __Auth__: Now includes an [OpAuth](http://opauth.org/) interface to integrate OAuth authentication with Simpleauth or Ormauth.
* __Auth__: When running migrations for Ormauth, groups and roles created now mimic the functionality of Simpleauth.
* __Email__: Support added for pipelining, sending multiple emails out over a single connection. Currently only supported by the SMTP driver.
* __Oil__: Command processor updated to work from Windows' powershell.
* __Oil__: The `refine()` method of the Command processor now accepts arguments so you can call it from code with the same arguments as from the commandline.
* __Oil__: New "--csrf" switch adds the CSRF token to generated forms.
* __Oil__: CSS in generated forms has been updated to the new Bootstrap version.
* __Oil__: Generated Admin code now has support for Ormauth.
* __Orm__: You can now create new EAV attributes by simply assigning a value to a new property (note: this disables Custom data for models with EAV support!).
* __Orm__: You can now use `unset()` to delete an EAV attribute.
* __Orm__: Several bugfixes in Model_Soft and Model_Temporal.
* __Orm__: New "Nestedset" Model to work with nested sets (hierarchical structures).
* __Orm__: No longer signals an insert failure if you don't use auto-increment PK's.
* __Orm__: Observer_Slug now works correctly with Model_Temporal.
* __Orm__: Added `count()`, `min()` and `max()` support to Model_Soft and Model_Temporal.
* __Orm__: Complex `find_this_and_that_or_other()` calls now work correctly.
* __Parser__: You can now call `Markdown::parse()` from within a Twig template.
* __Parser__: You can now call `Session::get_flash()` and `Session::set_flash()` from within a Twig template.

## v1.6

[Full List of core changes since 1.5](https://github.com/fuel/core/compare/1.5/master...1.6/master)

### Important fixes, changes, notes. Read them carefully.

* This release officially introduces Composer to FuelPHP. You will __have__ to install it, and run a 'php composer.phar update' to pull in any required packages. Without this step, __1.6 WILL NOT WORK!!!__
* Class names in the __Auth__ package have been modified to match FuelPHP coding standards. Check your configuration ('SimpleAuth' is now 'Simpleauth'!) and any class extensions you have made.
* The __Log__ functionality has been moved back in the core. If you are upgrading from 1.5, please remove the old 'Log' package from the ``always_load`` section in your ``config.php``, and remove the package from the packages folder.
* The environment 'stage' has been renamed to 'staging', the corresponding constant to Fuel::STAGING.
* You now get a proper error message if your PHP timezone settings are not correctly configured.
* You now get a proper error message if there is an issue with rights to the log file.
* All code that creates files or directories has been rewritten to properly set the configured permission mask without using `umask()`, which is not thread-safe.

### Backward compability notes

* The names of the __Auth__ classes have been changed to comply with FuelPHP coding standards (`Auth_Login_Simpleauth` instead of `Auth_Login_SimpleAuth`), this can cause a class-not-found error if you have extended an Auth class in your application.
* The ORM `validation_observer` now has multiple events. Do not define it without specifying which events to call, as it would cause validation to be called twice!
* The __Orm__ behaviour with regards to relation assignments has been changed. Now, when you do an unset(), a set to NULL or array(), or you assign a new value, the previous relation will be unset. Regardless of whether you had fetched that relation or not. This might impact your application if you have used this 'bug' as a shortcut to adding additional objects to an existing relation!

### Removed code (because it was deprecated in v1.5 or earlier)

* __Orm__: `find()` and `find(null)` functionality is now removed. Use `query()` instead.

### Code deprecated in v1.6 (to be removed in the next release)

* __ViewModel__: when determining the name of the ViewModel class to load, it will search for classes with and without the 'View_' prefix. This behaviour is deprecated, as of the next release ViewModel classes MUST be in classes/view, and MUST be prefixed with 'View_'.

### Security related

* The default security filters have been removed from the core configuration, to allow you to define your own security filters. **Note:** if you're migration from previous versions and relied on this default config, make sure your app config file has the default security filters defined!

### System changes

* The Markdown library has been upgraded to 1.2.6.
* The cache option in the global configuration now correctly caches finder paths to speedup file lookups.
* Controller methods can now return 'false' or 'array()' as valid values, for use in HMVC requests.
* Exceptions in shutdown event are now properly caught and handled.

### Specific classes

* __Agent__: will now re-use an expired download if a new browscap file could not be downloaded.
* __Arr__: New `search` method allows you to search for values in array structures, and get the (dot-notated) key returned.
* __Arr__: New `unique` method allows you to de-dup an array. Like array_unique(), but this one supports objects and closures, and doesn't sort the source array first.
* __Arr__: New `sum` method allows you to sum up specific values in a multi-dimensional array structure.
* __Asset__: now generates the correct Asset URL when using a CDN.
* __Controller_Rest__: now allows auth checks using a controller method (avoids `before()` or `router()` hacks).
* __DB__: new `identifier` method allows you to properly quote an identifier for use in custom queries.
* __DB__: the `Database_Transaction` class that was already deprecated in v1.2 has been removed. All drivers support transactions natively.
* __DButil__: now supports the keyword "PRIMARY KEY" on field updates.
* __Cache__: index mechanism has been refactored. Dependency checking now works properly when using APC, Memcached or Redis backends.
* __Error__: a new configuration option allows you to render already generated output to be shown in error messages via the 'prior output' link, instead of the HTML.
* __File__: `create_dir` method now works properly on Windows.
* __Form__: `select` now doesn't use inline css to generate optgroups unless needed.
* __Fieldset__: fixed generation of invalid labels.
* __Html__: `anchor` method now generates URL's without a trailing slash.
* __Input__: `uri` method now works properly on Windows.
* __Lang__: `delete` method now works properly when passing a $group value.
* __Pagination__: now generates the last link correctly.
* __Profiler__: DB query profiling now includes a stack trace for every query to make it easier to find it in your code.
* __Router__: now supports protocol specific routes (http/https) in verb based route notation.
* __Upload__: has been rewritten to use the FuelPHP v2 composer library.
* __Viewmodel__: now calls `before` before rendering the view, instead of when constructing the object.
* __Viewmodel__: ViewModel class name is now correctly determined from the passed view name.
* __Viewmodel__: Will now look in the global namespace for the ViewModel class if called from a module and not found in the module namespace.

### Packages

* __Auth__: Class names have been modified to match FuelPHP coding standards.
* __Auth__: `update_user` now verifies if the new email address is unique before updating it.
* __Auth__: Number of PBKDF2 iterations can now be configured in the auth config file.
* __Auth__: Multiple concurrent user logins can now be configured through the driver configuration file.
* __Auth__: Auth login drivers now set 'updated_at' correctly.
* __Auth__: new `get` method allows unified access to all user properties.
* __Auth__: new `groups` method which returns the list of all defined groups.
* __Auth__: new `roles` method which returns the list of all defined roles.
* __Auth__: new 'Ormauth' driver set that uses the database through ORM as datastore.
* __Auth__: Package now contains migrations for both Simpleauth and Ormauth.
* __Auth__: New 'Simple2Orm' task can migrate your existing Simpleauth config to Ormauth.
* __Email__: Attachments can now be named.
* __Log__: The Log package, introduced in 1.5 as a temporary solution, has been removed again.
* __Oil__: Fixed redirect loop in the generated admin backend code.
* __Oil__: Improved exception handling and reporting.
* __Oil__: Added support for ORM soft-delete models.
* __Oil__: Modified the scaffolding templates to work better with bootstrap.
* __Oil__: New commandline options for PHPunit allow for more granular testing and logging.
* __Orm__: Validation observer now supports 'before_insert' and 'before_update'.
* __Orm__: Now correctly resets foreign keys if cascade_delete is false.
* __Orm__: Added view support to count(), min() and max() queries.
* __Orm__: min() and max() now return integers instead of strings.
* __Orm__: Added temporal support (data versioning).
* __Orm__: You can now test for existence of EAV attributes using isset().
* __Orm__: Validation observer can now validate on insert and update too.
* __Orm__: It is now allowed for models to have a FK as part of the PK.
* __Orm__: You can now order a many_many result on an attribute in the through table.
* __Orm__: You can now pass custom (non-column) data when forging a new model object.
* __Orm__: Current relations are now properly unset when using unset() or a new assignment.
* __Orm__: `from_array` now returns $this so you can chain on it.
* __Orm__: `from_array` now allows you to load custom data.
* __Orm__: `from_array` now allows you to load related objects from a multidimensional array.
* __Orm__: Several speed improvements in Observer_Typing.
* __Orm__: Observer_Typing float conversions are now locale aware.
* __Orm__: Observer_Typing now uses property defaults on null values if defined.
* __Orm__: Observer_Typing can now handle MySQL '0000-00-00 00:00:00' datetime values.
* __Orm__: new `from_cache` method allows you to enable/disable object caching on a query.
* __Orm__: 'join on' now works correctly as documented.
* __Orm__: 'order_by' now works correctly when a subquery is generated.
* __Orm__: `is_changed` now does loose-typing, so 1 => '1' doesn't trigger an update query anymore.
* __Parser__: Added support for mthaml (HamlTwig)
* __Parser__: Switched to using Composer for smarty, mustache, mthaml and twig template engines.
* __Parser__: Markdown has been upgraded to 1.2.6.

## v1.5

[Full List of core changes since 1.4](https://github.com/fuel/core/compare/1.4/master...1.5/master)

### Important fixes, changes, notes. Read them carefully.

* The "Undefined constant MYSQL_ATTR_COMPRESS" issue that pops up under certain conditions has been fixed.
* It has been reported that under certain circumstances there might be issues with serialized data stored in the Auth user table, field "profile_fields", and the "payload" field in the sessions table. It is strongly advised to define those columns as "blob" to avoid these issues.
* A new `Log` package has been introduced in preparation for the transition to 2.0, which replaces the `Log` class.

### Backward compability notes

* __Uri::to_assoc()__ no longer throws an exception with uneven segments, but returns ``null`` as value of the last segment
* ORM __Model::find()__ no longer accepts ``null`` as only parameter. If you want to use that, you are now REQUIRED to also pass the options array (or an empty array).
* __Sessions__ have been refactored, all validation and validation data has been moved server side. Because of this, pre-1.5 sessions are not longer compatible.
* The __Log__ class has been removed and replaced by the __log package__. If you have extended the `Log` class in your application, you will have to extend `\Log\Log` instead, and check the compatibility of your changes. If they are about logging to other locations, you might want to look into the Monolog stream handlers instead.

### Removed code (because it was deprecated in v1.4 or earlier)

* ORM __Model::find()__ can no longer be used to construct queries using method chaining. Use  __Model::query()__ instead.

### System changes

* __Controller_Hybrid__: Now sets the correct content-type header on empty responses.
* __Controller_Rest__: Now sets the correct content-type header on empty responses.

### Specific classes

* __Agent__: Will now honour 301/302 redirects when trying to fetch the browscap file.
* __Arr__: New ``filter_recursive`` method, a recursive version of PHP's ``array_filter()`` function.
* __Debug:__ ``dump()`` method now html encodes string variables.
* __Debug:__ ``dump()`` and ``inspect()`` can now be styled using CSS (a classname has been added to the div).
* __Fieldset__: New ``set_tabular_form()`` method allows creation of one-to-many forms.
* __Fieldset__: New ``get_tabular_form()`` method to check if a fieldset defines a tabular form.
* __Image__: New ``flip()`` method for vertical/horizontal image flipping.
* __Inflector__: ``friendly_title()`` now has an option to deal with non-ascii characters.
* __Inflector__: ``pluralize()`` now has an count parameter to return a singular value if the count is 1.
* __Migrate__: Now allows you to define the DB connection to be used for migrations in the global migrations config file.
* __Model_Crud__: Now has a `$_write_connection` property to support master/slave database setups.
* __Mongo_Db__: Will now log it's queries to the profiler if enabled.
* __Mongo_Db__: Now has a method ``get_cursor()`` to directly get a mongodb cursor.
* __Pagination__: Now support pagination using a Query String variable.
* __Pagination__: Now has support for first/last page links.
* __Response__: Will now add a "Content-Length" header when generating the output.
* __Session__: Now correctly erases the session cookie on a ``destroy``.
* __Session__: Now silently (re)creates the session if data is present by no session is created.
* __Session__: Cookie encryption can now be disabled using a session configuration key.
* __Session__: Session cookie now only contains the session id. Validation now happens with server-side data.
* __Session__: New configuration key `expire_flash_after_get` controls `get_flash()` expiration.
* __Session__: ``get_flash()`` now has to override the configured flash variable expiration rules.
* __Session__: ``set_flash()`` now has to partial array dot-notation support.
* __Uri__: ``to_assoc()`` now accepts a start parameter allowing you to skip leading segments.
* __Validation__: Now has a new built-in rule 'numeric_between' allowing you to specify a range.
* __Database_Query_Builder_Join__: Now supports both AND and ON chaining of join condition.

### Packages

* __Orm__: Supports the new tabular form fieldset in it's models.
* __Orm__: ``find()`` options array now has support for 'group_by'.
* __Orm__: New ``Model_Soft`` implements soft-delete functionality (thanks to Steve West).
* __Orm__: ``from_array()`` can now also populate related objects.
* __Orm__: `Model` now has a `$_write_connection` property to support master/slave database setups.
* __Oil__: ``oil install`` now installs packages without 'fuel_' prefix too.
* __Oil__: scaffolding now supports subdirectories.
* __Oil__: Now has a config file that allows you to configure the location of phpunit.
* __Oil__: Now has a task `fromdb` that can generate models, migrations, scaffolding or admin from an existing database.
* __Parser__: Twig driver has been updated to work with Twig v1.12.0.

## v1.4

[Full List of core changes since 1.3](https://github.com/fuel/core/compare/1.3/master...1.4/master)

### Important fixes or changes

* fixed DB class error about missing __PDO::MYSQL_ATTR_COMPRESS__ constant
* you are now __REQUIRED__ to set a correct php timezone. The FuelPHP default value of 'UTC' has been removed, as it would cause date conversion errors that are difficult to find. Most notable, you will have issues with session and cookie expiration.
* __ALL__ default configuration has been moved to core/config. Only use the app/config folder for application specific overrides of default values, or for custom configuration.

### Backward compability notes

This release features a new Pagination class that isn't completely backward compatible with the API from previous versions. We have put a lot of effort in emulating the old behaviour of the class, but as PHP doesn't support magic getters/setters for static properties, you'll have to replace those in your code manually when you upgrade to v1.4. The required changes can be found in the [documentation](http://docs.fuelphp.com/classes/pagination.html).

### Removed code (because it was deprecated in v1.3)

* Removed "auto_encode_view_data" config key, deprecated in v1.2
* __Fuel__: Removed ``Fuel::add_module()``, deprecated in v1.2. Use ``Module::load()`` instead.
* __Fuel__: Removed ``Fuel::module_exists()``, deprecated in v1.2. Use ``Module::exists()`` instead.
* __Theme__: Removed ``$theme->asset()``, deprecated in v1.2. Use ``$theme->asset_path()`` instead.
* __Theme__: Removed ``$theme->info()``, deprecated in v1.2. Use ``$theme->get_info()`` instead.
* __Theme__: Removed ``$theme->all_info()``, deprecated in v1.2. Use ``$theme->load_info()`` instead.
* __Orm\Model__ : Removed ``$model->values()``, deprecated in v1.3. Use ``$model->set()`` instead.

### Code deprecated in v1.4 (to be removed in the next release)

* __Redis__: ``Redis::instance()`` will no longer create new objects. Use ``Redis::forge()`` for that.
* __Orm\Model__: Using the ``find()`` method without parameters is deprecated. Use ``query()`` instead.

### System changes

* __Config__ and __Lang__ loading with forced reload now bypasses the file cache and always reload.
* __Controller_Hybrid__: Is now fully hybrid, with support for get/post methods, and no longer restricted to ajax calls when returning json.
* __Fieldset__, __Form__ and __Validation__ now have full support for input tags using array notation.
* __Input__ and __Route__ now support a new configuration key ``routing.strip_extension`` to control wether or not the extension must be stripped from the URI.
* __Lang__: fixed double loading of language files when the active and fallback language are the same.
* __Pagination__: Class completely rewritten, now with instance and template support.
* __Uri__: Has improved extension processing, and now handles dots in URI parameters correctly.
* The active language is now a per-request setting instead of a global setting. Changing it in an HMVC request will no longer affect the language setting of the parent request.

### Specific classes

* __Arr__: New ``filter_suffixed()`` method to filter an array on key suffix.
* __Arr__: New ``remove_suffixed()`` method to remove keys from an array based on key suffix.
* __Asset__: DOCROOT can now be specified as the asset root path (by using "").
* __Controller_Rest__: Now allows you to specify a basenode when returning XML.
* __DB__: ``select()`` now has an option to reset previous selects.
* __DB__: Added ``error_info()`` to return information about the last error that occurred.
* __DB__: ``join()`` can now be used without conditions for a full join.
* __DB__: ``group_by()`` now supports passing an array of columns.
* __Fieldset__: New ``enable()``/``disable()`` methods to control which fields will be build.
* __Fieldset__: New ``get_name()`` method allows retrieval of the fieldset object name.
* __Fieldset__: ``set_config()`` and ``get_config()`` now support dot-notation for accessing config values.
* __Finder__: Fixed PHP notices after removing a finder search path.
* __Format__: Added JSONP support.
* __FTP__: Now supports a timeout on the connect.
* __Image__: Fixed forcing an image extension when using ImageMagick.
* __Inflector__: ``friendly_title()`` now has the option not to filter non-latin characters.
* __Input__: Fixed skipping IP validation when reserved_IP ranges were excluded.
* __Lang__: Now supports multiple languages concurrently. Loaded files for a given language code will no longer be overwritten when you switch the active language.
* __Lang__: ``load()`` method now also returns the loaded group on subsequent calls.
* __Markdown__: Has been upgraded to v1.2.5.
* __Migrate__: Fixed PHP notice when a non-existent package was specified.
* __Migrate__: An up or down migration can now be rejected by returning ``false``.
* __Migrate__: Added support for processing out-of-sequence migrations.
* __Redis__: Now has a ``forge()`` method to create multiple instances.
* __Redis__: Added support for Redis authentication.
* __Response__: If the body contains an array it will be converted to a string representation before outputting it.
* __Response__: ``redirect()`` now supports wildcards in the URL.
* __Router__: Re-introduced support for routing using URI extensions.
* __Session__: Fixed passing a session cookie via POST to allow access to the session by flash objects.
* __Session__: Added support for dot_notation to ``get_flash()``.
* __Session__: Fixed flash variables not being stored when retrieved in the same request.
* __Session__: Fixed session key data not available for new sessions until after a page reload.
* __Str__: Now has an ``is_xml()`` method.
* __Theme__: Is now module aware, and can prefix view paths with the current module name.
* __Upload__: ``process()`` now throws an exception if ``$_FILES`` does not exist (due to missing form enctype)
* __Uri__: New ``segment_replace()`` method allows for replacement of wildcards by current segments.
* __View__: ``get()`` now returns all variables set when no variable name is given.
* __Viewmodel__: ``get()`` now returns all variables set when no variable name is given.

### Packages

* __Auth__: No changes.
* __Email__: Added a Noop dummy driver, which can be used to prevent test emails going out.
* __Oil__: Added "generate TASK" option to generate task classes.
* __Oil__: Added support for Viewmodels to scaffolding.
* __Oil__: Fixed errors on ``false`` results in the console.
* __Oil__: Added support for "drop_{field}_from_{table}" to migrations.
* __Oil__: oil -v now also displays the current environment setting.
* __Oil__: New --singular option to force the use of singular names in scaffolding.
* __Orm__: Fixed PK overwrite issue when PK is not auto_increment.
* __Orm__: Observer_Slug now supports the ``before_update`` trigger.
* __Orm__: Added support for filter conditions to the model through the ``$_conditions`` property.
* __Orm__: Fixed incorrect sequence of multiple ``order_by()`` clauses.
* __Orm__: Implemented full support for partial selects.
* __Orm__: Fixed circular reference problem when using ``to_array()`` with included relations that self reference.
* __Orm__: ``get_one`` now uses ``rows_limit()`` instead of ``limit()`` when set.
* __Orm__: Model objects now support custom properties
* __Orm__: Added support for custom properties to ``to_array()``
* __Orm__: ``is_changed()`` now deals better with null values.
* __Orm__: Introduced support for EAV containers (emulation of EAV via one or more related tables)
* __Orm__: ``get_diff()`` now deals better with unset relations.
* __Orm__: Relations of new objects can now be fetched if the FK is known.
* __Orm__: Added support for ``group_by()``.
* __Parser__: ``forge()`` functionality now equals that of ``View::forge()``.
* __Parser__: Markdown has been upgraded to v1.2.5.

## v1.3

[Full List of core changes since 1.2](https://github.com/fuel/core/compare/1.2/master...1.3/master)

### Removed code (because it was deprecated in v1.2)

* __Controller__: Deprecated `$response` property has been removed from all base controller classes. All controller actions now HAVE TO return their results, either a `Response` object, or something that can be cast to string. If you are still on pre v1.2 controller code, your application will **NO LONGER** work after the upgrade to v1.3.

### Code deprecated in v1.3 (to be removed in v1.4)

* __Orm__: Model method `values()` has been deprecated. Use `set()` instead.

### Security related

* __PHPSecLib__: Has been updated to v0.2.2.
* __HTMLawed__: Has been updated to v1.1.12.

### System changes

* __Debug___: You can now modify the default display behaviour of `dump()` through `Debug::$js_toggle_open`.
* __Upload__: Now allows you to set custom messages in validation callbacks.
* __Config__: `Config::load` now always returns the loaded configuration.
* __Pagination__: Now uses anchors for all pagination enties, which allows for better styling.

### Specific classes

* __Arr__: `Arr::pluck` has been added.
* __Arr__: `Arr::remove_prefixed` has been added.
* __Arr__: `Arr::insert_assoc` has been added.
* __Asset__: Has been updated to work better on Windows.
* __Asset__: `Asset::find_file` has been added.
* __Asset__: `Asset::add_type` has been added.
* __DB__: `DB::in_transaction` has been added.
* __DB__: Added support for compressed MySQL connections through the new `compress` config key.
* __Error__: PHP notices/warnings/errors are now caught and thrown as an Exception.
* __Event__: The Event class has been converted to be instance based.
* __Fieldset__: You can now choose to overwrite existing options when using `set_options`.
* __File__: download() has been made to work when shutdown events are defined that set headers.
* __Image__: New option on load() to force a file extension.
* __Format__: CSV file handling has been improved.
* __Log__: Now supports custom log levels.
* __Log__: Now allows you to configure an array of specific log levels to log.
* __Migrate__: Now supports multiple package paths.
* __Mongo_Db__: `Mongo_Db::get_collection` has been added.
* __Pagination__: Added `attrs` keys to the configuration to define custom anchor attributes.
* __Redis__: Added support for connection timeouts through the new `timeout` config key.
* __Str__: `Str::starts_with` has been added.
* __Str__: `Str::ends_with` has been added.
* __Str__: `Str::is_json` has been added.
* __Str__: `Str::is_html` has been added.
* __Str__: `Str::is_serialized` has been added.

### Packages

* __Auth__: `get_profile_fields()` now allows you to fetch a single profile field.
* __Email__: New `NoOp` email driver allows testing without sending emails out.
* __Oil__: Now returns a non-zero exit code on failures.
* __Oil__: Added support for PHPunit clover, text and phpformat Code Coverage methods.
* __Orm__: New model method `register_observer()` and `unregister_observer()` to define new observers at runtime.
* __Orm__: Added support for `where` and `order_by` clauses to relation conditions.
* __Orm__: `set()` method has been updated to provide the same API as **Model_Crud**.
* __Orm__: PK's are now typecast on retrieval if a type has been defined in the properties.
* __Orm__: Update query code has been improved for better support of PostgreSQL.
* __Parse__: Smarty driver now supports the `plugin_dir` path.

## v1.2

[Full List of core changes since 1.1](https://github.com/fuel/core/compare/1.1/master...1.2/master)

### Removed code (because it was deprecated in v1.1)

* All `factory()` methods. The have been replaced by `forge()`.
* __Agent__::is_mobile(). Replaced by `is_mobiledevice()`.
* __Arr__::element(). Replaced by `get()`.
* __Arr__::elements(). Replaced by `get()`.
* __Arr__::replace_keys(). Replaced by `replace_key()`.
* __Controller__::render(). Is no longer used as actions need to return a Response object now.
* __Database_Connection__::transactional(). Was already a NOOP.
* __DB__::transactional(). Called Database_Connection::transactional().
* __Fieldset__::errors(). Replaced by `error()`.
* __Fieldset__::repopulate(). Undocumented parameter was removed, functionality is offered by `populate()`.
* __Fuel__::find_file(). Replaced by `Finder::search()`.
* __Fuel__::list_files(). Replaced by `Finder::instance()->list_files()`.
* __Fuel__::add_path(). Was used by `find_file()`, no longer needed.
* __Fuel__::get_paths(). Was used by `find_file()`, no longer needed.
* __Fuel__::add_package(). Replaced by `Package::load()`.
* __Fuel__::remove_package(). Replaced by `Package::unload()`.
* __Fuel_Exception__ class. Replaced by `FuelException`.
* __Input__::get_post(). Replaced by `param()`.
* __Lang__::line(). Replaced by `get()`.
* __Request404Exception__ class. Is replaced by `HttpNotFoundException`.
* __Uri__ properties $uri and $segments are now protected. Use Uri::get() and Uri::get_segment() or Uri::get_segments().
* __Validation__::errors(). Replaced by `error()`.
* __Viewmodel__ property $_template. Is replaced by `$_view`.
* __Viewmodel__::set_template(). Replaced by `set_view()`.

### Code deprecated in v1.2 (to be removed in v1.3)

* __Pagination__: Class will be removed and replaced by a new `Paginate` class.
* __Fuel__::add_module(). Is replaced by `Module::load()`.
* __Fuel__::module_exists(). Is replaced by `Module::exists()`.
* __Theme__::asset(). Replaced by `asset_path()`.
* __Theme__::info(). Replaced by `get_info()`.
* __Theme__::all_info(). Replaced by `load_info()`.

### Security related

* Security class now __requires__ you to define the `security.output_filter` application config setting. An exception is thrown if it isn't present.
* Security::htmlentities() now defaults to use ENT_QUOTES instead of ENT_COMPAT as flag. This is configurable in the second argument for the method and the default can be overwritten in config as `security.htmlentities_flags`.

### System changes

* __Controller__: action methods, or the controllers `after()` method if present, now must return a `Response` object.
* __Controller__: `before()` and `after()` methods are now optional, as documented.
* __Controller_Hybrid__: combines `Controller_Template` and `Controller_Rest` in a single base controller for mixed HTTP and REST responses.
* __Controller_Rest__: added a fallback to `"action_"` when no HTTP method action is found.
* __Controller_Rest__: you can now define custom HTTP status codes.
* __Controller_Template__: the `$auto_render` setting has been removed, to prevent rendering return whatever you want to use instead.
* __Database__: The PDO driver now supports `list_columns()`.
* __Module__: new `Module` class to load or unload modules.
* __Uri__: the URL extension is no longer part of the URI. A new `extension()` method allows you to fetch it.
* __Request__: `Request_Curl` now properly deals with succesful requests that return a 4xx or 5xx HTTP status.
* __Request__: `Request_Curl` and `Request_Soap` now supports returning header information. A `get_headers()` has been added to fetch them manually.
* __Router__: can now be configured to treat URI's without regards to case.

### Specific classes

* __Arr__: `Arr::to_assoc()` now throws a BadMethodCallException on bad input.
* __Arr__: `Arr::assoc_to_keyval()` now requires all parameters and first parameter must be an array or implement `Iterator`.
* __Arr__: Added `reverse_flatten()`, `is_assoc()` and `insert_before_key()` methods.
* __Arr__: Added `in_array_recursive()` to do a recursive `in_array()` lookup.
* __Asset__: Separated into the static front (`Asset`) and dynamic instance (`Asset_Instance`).
* __Asset__: Separated into the static front (`Asset`) and dynamic instance (`Asset_Instance`).
* __Asset__: `css()`, `js()` and `img()` methods are now chainable.
* __Asset__: you can now specify a URL as location, for CDN support.
* __Asset__: new `fail_silently` config value allows you to skip missing assets.
* __Cli__: now supports ANSICON on Windows for colored commandline output.
* __Config__: is now driver based to support `php`, `ini`, `yaml` and `json` type configs.
* __Config__: now allow you to load a file by FQFN.
* __Cookie__: all cookie data can now be fetched like Input class does.
* __Date__: All fuel notices have been replaced by `UnexpectedValueException`s.
* __Date__: On windows an extra fallback has been added for the `create_from_string()` method.
* __Date__: new `display_timezone()' and `get_timezone_abbr()`, and changes to support working with multiple timezones.
* __DB__: `cache()` now has the option not to cache empty resultsets.
* __DB__: `where()` do now support closures to specify the where clause.
* __DB__: Update now supports `limit()` and `order_by()`.
* __DB__: now tries to reconnect when a disconnected DB connection is detected.
* __DButil__: `create_database()` now supports 'IF NOT EXIST'.
* __DButil__: Better support for the CONSTRAINT keyword.
* __DButil__: new `add_foreign_key()` and `drop_foreign_key()` methods.
* __Event__: shutdown events are now also executed after `exit` and `die` statements.
* __Fieldset__: added `set_fieldset_tag()` to define the fieldset tag.
__Fieldset__: added `add_before()` and `add_after()` methods to insert a new field before/after a specific field.
* __Fieldset_Field__: added `add_description()` method and `{description}` tag to templates.
* __Fieldset_Field__: added `add_error_message()` method to create error message overwrites per field.
* __File__: `download()` now allows you to continue processing after calling it.
* __Form__: Separated into the static front (`Form`) and dynamic instance (`Form_Instance`).
* __Inflector__: now supports Hungarian accepted characters when converting to ascii.
* __Input__: `method()` now supports the `X-HTTP-Method-Override` header.
* __Input__: new `json()` and `xml()` methods to fetch json or xml from the HTTP request body.
* __Lang__: `load()` method now supports overwriting when merging language files.
* __Lang__: now allow you to load a file by FQFN.
* __Lang__: is now driver based to support `php`, `ini`, `yaml` and `json` type language files.
* __Lang__: language files can now be saved (as `php`, `ini`, `yaml` or `json`) using `save()`.
* __Migrate__: now tracks individual migrations, so they don't have to have a sequence number anymore.
* __Model_Crud__: now supports `created_at` and `updated_at` fields, like `ORM\Model` does.
* __Model_Crud__: now has full callback support.
* __Model_Crud__: you can now run validation separately (`::validates`) and skip validation when saving a model.
* __Profiler__: profiler logging methods are now NO-OP's when the profiler is not loaded.
* __Profiler__: now writes it's output under the page content, instead of using an overlay.
* __Session__: Added session task to create and remove sessions table.
* __Session__: New sessions are not saved until there is data present in the session.
* __Theme__: Separated into the static front (`Theme`) and dynamic instance (`Theme_Instance`).
* __Theme__: now supports installation outside the docroot (for views).
* __Theme__: now uses the `Asset` class to load theme assets.
* __Theme__: instances now support templates, template partials and partial chrome templates.
* __Validation__: You can now disable fallback to global input using the 'validation.global_input_fallback' config setting.

### Packages

* __Auth__: Auth login drivers no have a `validate_user` method to validate a user/password without setting up a logged-in session.
* __Auth__: SimpleAuth `SimpleUserUpdateException`s are now numbered to be able to identify the exact error after catching the exception.
* __Email__: Now handles SMTP timeouts properly.
* __Email__: You can now specify the return address.
* __Email__: Now handles BCC lists correctly when using SMTP.
* __Email__: Respects new lines in alt body better.
* __Email__: You can now specify the return address.
* __Oil__: Use `phpunit.xml` from `APPPATH` if present when running unit tests.
* __Oil__: Reinstated `oil package` command to install packages from git repositories.
* __Oil__: You can define the environment the command has to run in using the `-env` commandline switch.
* __Oil__: Scaffolding now supports both `Model_Crud` and `Orm\Model`.
* __Oil__: Scaffolding now supports adding created-at and updated-at.
* __Oil__: Scaffolding now supports skipping the creation of a migration file using `-no-migration`.
* __Oil__: There is now a core task to generate the table for the database session store.
* __Orm__: New model method `is_fetched()` checks if relation data is fetched without triggering a new query.
* __Orm__: Validation section of the properties has a new key `skip` to indicate the field should not be validated.

## v1.1

[Full List of core changes since 1.0.1](https://github.com/fuel/core/compare/1.0/master...1.1/master)

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
