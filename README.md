# ArtisanPack UI Code Standards

A custom PHP code style standard based on PHPStorm settings. This package provides custom sniffs for PHP_CodeSniffer that enforce consistent code style across your PHP projects.

## Installation

You can install the ArtisanPack UI Code Standards package by running the following composer command:

```bash
composer require artisanpack-ui/code-style --dev
```

## Usage

### Configuration

After installation, you can create a `phpcs.xml` file in your project root with the following content:

```xml
<?xml version="1.0"?>
<ruleset name="YourProjectStandard">
    <description>Your project's coding standard</description>

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
```

### Running PHP_CodeSniffer

You can run PHP_CodeSniffer with the ArtisanPackUI standard using the following command:

```bash
./vendor/bin/phpcs --standard=ArtisanPackUIStandard .
```

Or if you've set up a custom `phpcs.xml` file:

```bash
./vendor/bin/phpcs
```

### Custom Sniffs

This package includes the following custom sniffs:

1. **Indentation**: Ensures that indentation is done with real tabs instead of spaces, except when aligning variable assignments and array item definitions.
2. **LineLength**: Ensures that lines do not exceed a specified length (120 characters by default).
3. **Braces**: Ensures that braces are placed correctly in various code constructs (on the same line as the declaration by default).
4. **Spacing**: Ensures consistent spacing around operators, parentheses, brackets, and braces. Enforces spaces after opening brackets, braces, and parentheses, and spaces before closing brackets, braces, and parentheses. Also enforces space between if, elseif, for, while, foreach, etc. and the opening parenthesis, and space between closing parenthesis and opening bracket.
5. **Alignment**: Ensures that equal signs are aligned for variable assignments and array item definitions that are right next to each other.
6. **YodaConditionals**: Ensures that Yoda conditionals are used (e.g., `if (true === $condition)` instead of `if ($condition === true)`).
7. **PhpTags**: Ensures that opening and closing PHP tags are on separate lines, and that PHP tags are not used in Blade files.
8. **Quotes**: Ensures that single quotes are used if not escaping a variable, and double quotes are used if escaping a variable.
9. **NamingConventions**: Ensures that naming conventions are followed (Classes: PascalCase, Functions: camelCase, Variables: camelCase, Table columns: snake_case).
10. **ControlStructures**: Ensures that control structures follow the correct format (if : elseif : else format in template/Blade files, bracket format in all other files).
11. **ArraySyntax**: Ensures that arrays use the short syntax and that associative arrays with multiple items have each item on a new line.
12. **TypeDeclaration**: Ensures that all functions, parameters, and properties have type declarations unless it's not possible.
13. **MagicMethods**: Ensures that PHP magic functions are uppercase.
14. **ImportOrdering**: Ensures that imports are ordered correctly (Classes, Functions, Constants).
15. **ClassStructure**: Ensures that class structure follows the coding standards (Trait Use statements at the top of the class, visibility declared for all properties and methods, one class per file).
16. **DisallowedFunctions**: Ensures that certain disallowed functions are not used (e.g., `die`, `exit`, `var_dump`, `print_r`).

### Customizing Sniffs

You can customize the behavior of the sniffs by overriding their properties in your `phpcs.xml` file:

```xml
<rule ref="ArtisanPackUI.Formatting.Indentation">
    <properties>
        <property name="indent" value="2"/>
    </properties>
</rule>
```

## Contributing

As an open source project, this package is open to contributions from anyone. Please [read through the contributing
guidelines](CONTRIBUTING.md) to learn more about how you can contribute to this project.
