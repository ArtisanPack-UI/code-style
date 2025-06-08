# Custom Sniffs

The ArtisanPack UI Code Standards package includes a variety of custom sniffs that enforce consistent code style across your PHP projects. This document provides detailed information about each sniff and how it affects your code.

## Available Sniffs

### Indentation

Ensures that indentation is done with real tabs instead of spaces, except when aligning variable assignments and array item definitions.

**Example:**
```php
function example() {
	$variable = 'value';  // Indented with a tab
	
	// Aligned variable assignments
	$short  = 'value';
	$longer = 'another value';
}
```

### LineLength

Ensures that lines do not exceed a specified length (120 characters by default).

**Example:**
```php
// This line is too long and would trigger a warning or error if it exceeds the configured maximum line length (default: 120 characters)
$veryLongVariable = 'This is a very long string that would likely exceed the maximum line length if we continued to add more text to it.';

// Instead, break it up:
$veryLongVariable = 'This is a very long string that would likely exceed the maximum line length '
	. 'if we continued to add more text to it.';
```

### Braces

Ensures that braces are placed correctly in various code constructs (on the same line as the declaration by default).

**Example:**
```php
// Correct
if ($condition) {
	// code
}

// Incorrect
if ($condition) 
{
	// code
}
```

### Spacing

Ensures consistent spacing around operators, parentheses, brackets, and braces. Enforces spaces after opening brackets, braces, and parentheses, and spaces before closing brackets, braces, and parentheses. Also enforces space between if, elseif, for, while, foreach, etc. and the opening parenthesis, and space between closing parenthesis and opening bracket.

**Example:**
```php
// Correct
if ( $condition ) {
	$array = [ 'key' => 'value' ];
}

// Incorrect
if($condition){
	$array=['key'=>'value'];
}
```

### Alignment

Ensures that equal signs are aligned for variable assignments and array item definitions that are right next to each other.

**Example:**
```php
// Correct
$short  = 'value';
$longer = 'another value';

$array = [
	'short'  => 'value',
	'longer' => 'another value',
];

// Incorrect
$short = 'value';
$longer = 'another value';

$array = [
	'short' => 'value',
	'longer' => 'another value',
];
```

### YodaConditionals

Ensures that Yoda conditionals are used (e.g., `if (true === $condition)` instead of `if ($condition === true)`).

**Example:**
```php
// Correct
if ( true === $condition ) {
	// code
}

// Incorrect
if ( $condition === true ) {
	// code
}
```

### PhpTags

Ensures that opening and closing PHP tags are on separate lines, and that PHP tags are not used in Blade files.

**Example:**
```php
// Correct
<?php
// code
?>

// Incorrect
<?php // code ?>
```

### Quotes

Ensures that single quotes are used if not escaping a variable, and double quotes are used if escaping a variable.

**Example:**
```php
// Correct
$single = 'string';
$double = "string with $variable";

// Incorrect
$single = "string";
$double = 'string with ' . $variable;
```

### NamingConventions

Ensures that naming conventions are followed:
- Classes: PascalCase
- Functions: camelCase
- Variables: camelCase
- Table columns: snake_case

**Example:**
```php
// Correct
class MyClass {
	private $myVariable;
	
	public function myFunction() {
		$db_column = 'value';
	}
}

// Incorrect
class my_class {
	private $MyVariable;
	
	public function My_Function() {
		$dbColumn = 'value';
	}
}
```

### ControlStructures

Ensures that control structures follow the correct format (if : elseif : else format in template/Blade files, bracket format in all other files).

**Example:**
```php
// Correct for PHP files
if ( $condition ) {
	// code
} elseif ( $anotherCondition ) {
	// code
} else {
	// code
}

// Correct for template/Blade files
<?php if ( $condition ) : ?>
	<!-- code -->
<?php elseif ( $anotherCondition ) : ?>
	<!-- code -->
<?php else : ?>
	<!-- code -->
<?php endif; ?>
```

### ArraySyntax

Ensures that arrays use the short syntax and that associative arrays with multiple items have each item on a new line.

**Example:**
```php
// Correct
$shortArray = ['value1', 'value2'];

$associativeArray = [
	'key1' => 'value1',
	'key2' => 'value2',
];

// Incorrect
$oldArray = array('value1', 'value2');

$inlineAssociative = ['key1' => 'value1', 'key2' => 'value2'];
```

### TypeDeclaration

Ensures that all functions, parameters, and properties have type declarations unless it's not possible.

**Example:**
```php
// Correct
public function myFunction(string $param1, int $param2): bool {
	return true;
}

// Incorrect
public function myFunction($param1, $param2) {
	return true;
}
```

### MagicMethods

Ensures that PHP magic functions are uppercase.

**Example:**
```php
// Correct
public function __CONSTRUCT() {
	// code
}

// Incorrect
public function __construct() {
	// code
}
```

### ImportOrdering

Ensures that imports are ordered correctly (Classes, Functions, Constants).

**Example:**
```php
// Correct
use App\Models\User;
use App\Models\Post;
use function app;
use const APP_DEBUG;

// Incorrect
use const APP_DEBUG;
use App\Models\User;
use function app;
use App\Models\Post;
```

### ClassStructure

Ensures that class structure follows the coding standards (Trait Use statements at the top of the class, visibility declared for all properties and methods, one class per file).

**Example:**
```php
// Correct
class MyClass {
	use MyTrait;
	
	private $property;
	
	public function myMethod() {
		// code
	}
}

// Incorrect
class MyClass {
	public function myMethod() {
		// code
	}
	
	use MyTrait;
	
	var $property;
}
```

### DisallowedFunctions

Ensures that certain disallowed functions are not used (e.g., `die`, `exit`, `var_dump`, `print_r`).

**Example:**
```php
// Incorrect - these would trigger errors
die('Fatal error');
exit;
var_dump($variable);
print_r($array);

// Correct alternatives
throw new Exception('Fatal error');
return;
// Use proper logging instead of var_dump/print_r
Log::debug($variable);
```

## Further Customization

For information on how to customize these sniffs for your specific needs, please refer to the [Customization](customization.md) guide.