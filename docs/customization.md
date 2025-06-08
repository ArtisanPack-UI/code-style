# Customizing ArtisanPack UI Code Standards

This guide explains how to customize the ArtisanPack UI Code Standards to better fit your project's specific needs.

## Customizing Sniffs in phpcs.xml

You can customize the behavior of individual sniffs by setting properties in your `phpcs.xml` file. This allows you to adjust the standard without modifying the package itself.

### Basic Customization

Here's an example of how to customize the indentation sniff to use 2 spaces instead of tabs:

```xml
<?xml version="1.0"?>
<ruleset name="YourProjectStandard">
    <description>Your project's coding standard</description>

    <!-- Use ArtisanPackUI standard -->
    <rule ref="ArtisanPackUIStandard"/>

    <!-- Customize indentation -->
    <rule ref="ArtisanPackUI.Formatting.Indentation">
        <properties>
            <property name="indent" value="2"/>
            <property name="tabIndent" value="false"/>
        </properties>
    </rule>

    <!-- Specify paths to check -->
    <file>app</file>
    <file>src</file>
    <file>tests</file>

    <!-- Exclude paths -->
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/node_modules/*</exclude-pattern>
</ruleset>
```

## Common Customization Options

Here are some common properties you can customize for various sniffs:

### Indentation

```xml
<rule ref="ArtisanPackUI.Formatting.Indentation">
    <properties>
        <!-- Number of spaces for indentation (if not using tabs) -->
        <property name="indent" value="4"/>
        
        <!-- Whether to use tabs (true) or spaces (false) for indentation -->
        <property name="tabIndent" value="true"/>
    </properties>
</rule>
```

### LineLength

```xml
<rule ref="ArtisanPackUI.Formatting.LineLength">
    <properties>
        <!-- Maximum line length -->
        <property name="lineLimit" value="120"/>
        
        <!-- Whether to show warnings (true) or errors (false) for long lines -->
        <property name="warnOnly" value="true"/>
    </properties>
</rule>
```

### Braces

```xml
<rule ref="ArtisanPackUI.Formatting.Braces">
    <properties>
        <!-- Whether to require braces on the same line as the declaration -->
        <property name="sameLine" value="true"/>
    </properties>
</rule>
```

### Spacing

```xml
<rule ref="ArtisanPackUI.Formatting.Spacing">
    <properties>
        <!-- Number of spaces around operators -->
        <property name="operatorSpacing" value="1"/>
        
        <!-- Whether to require spaces inside parentheses -->
        <property name="spaceInsideParentheses" value="true"/>
    </properties>
</rule>
```

### YodaConditionals

```xml
<rule ref="ArtisanPackUI.Formatting.YodaConditionals">
    <properties>
        <!-- Whether to enforce Yoda conditionals -->
        <property name="enforce" value="true"/>
    </properties>
</rule>
```

## Excluding Sniffs

If you want to use the ArtisanPackUI standard but exclude specific sniffs, you can do so in your `phpcs.xml` file:

```xml
<?xml version="1.0"?>
<ruleset name="YourProjectStandard">
    <description>Your project's coding standard</description>

    <!-- Use ArtisanPackUI standard -->
    <rule ref="ArtisanPackUIStandard">
        <!-- Exclude specific sniffs -->
        <exclude name="ArtisanPackUI.Formatting.YodaConditionals"/>
        <exclude name="ArtisanPackUI.Formatting.MagicMethods"/>
    </rule>

    <!-- Specify paths to check -->
    <file>app</file>
    <file>src</file>
    <file>tests</file>

    <!-- Exclude paths -->
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/node_modules/*</exclude-pattern>
</ruleset>
```

## Excluding Files or Directories

You can exclude specific files or directories from being checked:

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
    <exclude-pattern>app/Legacy/*</exclude-pattern>
    <exclude-pattern>src/ThirdParty/*</exclude-pattern>
</ruleset>
```

## Creating a Custom Ruleset

For more advanced customization, you can create your own ruleset that extends the ArtisanPackUI standard:

1. Create a file named `MyStandard/ruleset.xml` in your project:

```xml
<?xml version="1.0"?>
<ruleset name="MyStandard">
    <description>My custom coding standard based on ArtisanPackUI</description>

    <!-- Include ArtisanPackUI standard -->
    <rule ref="ArtisanPackUIStandard">
        <!-- Exclude any sniffs you don't want -->
        <exclude name="ArtisanPackUI.Formatting.YodaConditionals"/>
    </rule>

    <!-- Add your own custom rules or include other standards -->
    <rule ref="PSR12.Classes.ClassDeclaration"/>
</ruleset>
```

2. Use your custom standard in your `phpcs.xml` file:

```xml
<?xml version="1.0"?>
<ruleset name="YourProjectStandard">
    <description>Your project's coding standard</description>

    <!-- Use your custom standard -->
    <rule ref="MyStandard"/>

    <!-- Specify paths to check -->
    <file>app</file>
    <file>src</file>
    <file>tests</file>

    <!-- Exclude paths -->
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/node_modules/*</exclude-pattern>
</ruleset>
```

## Troubleshooting

If you encounter issues with your customizations:

1. Run PHP_CodeSniffer with the `-v` flag to see more detailed output:

```bash
./vendor/bin/phpcs -v
```

2. Check that your XML syntax is correct.

3. Ensure that the properties you're trying to customize actually exist in the sniff.

For more help, refer to the [PHP_CodeSniffer documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki).