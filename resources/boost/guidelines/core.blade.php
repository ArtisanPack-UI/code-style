## ArtisanPack UI Code Standards

This package provides a custom PHP_CodeSniffer standard that enforces consistent code style across PHP projects. It includes 16+ custom sniffs covering formatting, code structure, naming conventions, security, and best practices.

### Installation & Configuration

After installing via `composer require artisanpack-ui/code-style --dev`, create a `phpcs.xml` file:

@verbatim
<code-snippet name="Basic phpcs.xml configuration" lang="xml">
<?xml version="1.0"?>
<ruleset name="ProjectStandard">
    <description>Project coding standard</description>

    <!-- Use ArtisanPackUI standard -->
    <rule ref="ArtisanPackUIStandard"/>

    <!-- Specify paths to check -->
    <file>app</file>
    <file>src</file>
    <file>tests</file>

    <!-- Exclude paths -->
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/node_modules/*</exclude-pattern>
</ruleset>
</code-snippet>
@endverbatim

### Running Code Style Checks

Run PHP_CodeSniffer with: `./vendor/bin/phpcs --standard=ArtisanPackUIStandard .` or simply `./vendor/bin/phpcs` if using a custom `phpcs.xml` file.

### Key Formatting Rules

- **Indentation**: Use real tabs (not spaces) for indentation, except when aligning variable assignments.
- **Braces**: Opening braces on the same line as declaration (`if ($condition) {`).
- **Spacing**: Spaces after opening and before closing brackets/braces/parentheses (`if ( $condition ) {`, `[ 'key' => 'value' ]`).
- **Alignment**: Align equal signs for consecutive variable assignments and array items.

@verbatim
<code-snippet name="Formatting example" lang="php">
// Correct formatting
function example() {
	$short  = 'value';
	$longer = 'another value';

	if ( $condition ) {
		$array = [ 'key' => 'value' ];
	}
}
</code-snippet>
@endverbatim

### Naming Conventions

- **Classes**: PascalCase
- **Functions/Methods**: camelCase
- **Variables**: camelCase
- **Database columns**: snake_case

@verbatim
<code-snippet name="Naming conventions" lang="php">
class MyClass {
	private $myVariable;

	public function myFunction() {
		$dbColumn = 'value'; // Use snake_case for DB columns
	}
}
</code-snippet>
@endverbatim

### Control Structures & Arrays

- Use Yoda conditionals: `if ( true === $condition )` instead of `if ( $condition === true )`
- Short array syntax: `[]` instead of `array()`
- Multi-item associative arrays on separate lines
- Template files use colon syntax (`if : endif`), regular PHP files use braces

@verbatim
<code-snippet name="Control structures and arrays" lang="php">
// Yoda conditional
if ( true === $condition ) {
	// code
}

// Array syntax
$shortArray = [ 'value1', 'value2' ];

$associativeArray = [
	'key1' => 'value1',
	'key2' => 'value2',
];
</code-snippet>
@endverbatim

### Type Declarations & Best Practices

- All functions must have return type declarations
- All parameters must have type declarations
- All properties must have type declarations (except in Laravel Models - automatically skipped)
- Avoid disallowed functions: `die`, `exit`, `var_dump`, `print_r`

@verbatim
<code-snippet name="Type declarations" lang="php">
public function myFunction(string $param1, int $param2): bool {
	return true;
}

// Instead of var_dump/print_r, use logging
Log::debug($variable);
</code-snippet>
@endverbatim

### Class Structure

- Trait use statements at the top of the class
- Visibility declared for all properties and methods
- One class per file
- Imports ordered: Classes, Functions, Constants

@verbatim
<code-snippet name="Class structure" lang="php">
use App\Models\User;
use App\Models\Post;
use function app;
use const APP_DEBUG;

class MyClass {
	use MyTrait;

	private string $property;

	public function myMethod(): void {
		// code
	}
}
</code-snippet>
@endverbatim

### Customizing Sniffs

Override sniff properties in your `phpcs.xml` file:

@verbatim
<code-snippet name="Customizing sniff properties" lang="xml">
<rule ref="ArtisanPackUI.Formatting.Indentation">
    <properties>
        <property name="indent" value="2"/>
    </properties>
</rule>
</code-snippet>
@endverbatim

### Security Features

The package includes security sniffs for input validation and output escaping. Always validate user input and escape output appropriately.
