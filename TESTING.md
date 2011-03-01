# Testing Fuel

Fuel uses [PHPUnit](https://github.com/sebastianbergmann/phpunit/) for it's Unit Testing needs.  It must be installed for the tests to run.

**NOTE: No code will be accepted without tests written.**

## Running Tests

Running the unit tests is as simple as navigating to the root install folder on the command line and running the following:

    $ phpunit

That's it!  You can also tell it specific groups (which we will get into in minute) to run.  For example to run only the core tests:

    $ phpunit --group Core

## Writing Tests

### Where do they go?

All tests are to go in the **tests** folders inside their respective parent folder.  For instance:

* App tests go in *fuel/app/tests*
* Core tests go in *fuel/core/tests*
* Package tests go in *fuel/packages/package_name/tests*

### File / Class Naming

The files must have the same name as the test class (Case Sensative).

Classes / File names **MUST** end in the word **Test** (Case Sensative).

Some example names:

    // Good
    ArrTest in fuel/core/tests/ArrTest.php
    ImageTest in fuel/core/tests/ImageTest.php
    FuelTest in fuel/core/tests/FuelTest.php
    
    // Bad
    Arrtests
    Somestuff

### Test Grouping

All tests inside the **core** folder must be in the **core** group.  A classes test's should also be grouped together under the name of the class.

Here is an example of a core class test with proper DocBlocks:

    /**
     * Arr class tests
     * 
     * @group Core
     * @group Arr
     */
    class ArrTest extends \PHPUnit_Framework_TestCase {

    	/**
    	 * Tests Arr::element()
    	 * 
    	 * @test
    	 */
    	public function test_element()
    	{
    		// Test code here
    	}
    }
    
All App tests should be in the **app** group.

### Namespaces

All **core** tests should be in the **Fuel\Core** namespace.  This is so that we are sure we are testing the core classes, 
not any extensions that may be in *app*.

App tests can be in any namespace.

### What class do I extend?

All tests should extend the **PHPUnit_Framework_TestCase** class.  **NOTE:** if you are in a namespace make sure you prepend a ** \ ** in front of the class name.

## Example

    namespace Fuel\Core;

    /**
     * Arr class tests
     * 
     * @group Core
     * @group Arr
     */
    class ArrTest extends \PHPUnit_Framework_TestCase {

    	/**
    	 * Tests Arr::flatten_assoc()
    	 * 
    	 * @test
    	 */
    	public function test_flatten_assoc()
    	{
    		$people = array(
    			array(
    				"name" => "Jack",
    				"age" => 21
    			),
    			array(
    				"name" => "Jill",
    				"age" => 23
    			)
    		);

    		$output = Arr::flatten_assoc($people);

    		$expected = array(
    			"0:name" => "Jack",
    			"0:age" => 21,
    			"1:name" => "Jill",
    			"1:age" => 23
    		);

    		$output = Arr::flatten_assoc($people);
    		$this->assertEquals($expected, $output);
    	}

    }

