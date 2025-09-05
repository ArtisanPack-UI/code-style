---
title: Usage Guide
---

# Usage Guide

This guide will help you understand how to use the ArtisanPack UI Code Standards package in your PHP projects.

## Basic Configuration

After [installing the package](../installation), you need to configure PHP_CodeSniffer to use the ArtisanPack UI standard.

### Creating a Configuration File

Create a `phpcs.xml` file in your project root with the following content:

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

This configuration tells PHP_CodeSniffer to:
1. Use the ArtisanPackUI standard
2. Check files in the `app`, `src`, and `tests` directories
3. Exclude files in the `vendor` and `node_modules` directories

## Running PHP_CodeSniffer

### Using the Command Line

You can run PHP_CodeSniffer with the ArtisanPackUI standard using the following command:

```bash
./vendor/bin/phpcs --standard=ArtisanPackUIStandard .
```

Or if you've set up a custom `phpcs.xml` file as described above:

```bash
./vendor/bin/phpcs
```

### Fixing Issues Automatically

PHP_CodeSniffer can automatically fix some coding standard violations. To do this, use the `phpcbf` command:

```bash
./vendor/bin/phpcbf --standard=ArtisanPackUIStandard .
```

Or with your custom configuration:

```bash
./vendor/bin/phpcbf
```

## Integration with IDEs

### PhpStorm

1. Go to Settings/Preferences > Editor > Inspections
2. Find PHP > Quality Tools > PHP_CodeSniffer validation
3. Check the "Enable" box
4. Set the "Coding standard" to "Custom" and select your `phpcs.xml` file

### Visual Studio Code

1. Install the "PHP Sniffer & Beautifier" extension
2. Configure it to use your `phpcs.xml` file

## Continuous Integration

### GitHub Actions

You can add PHP_CodeSniffer to your GitHub Actions workflow:

```yaml
name: PHP Code Style

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  phpcs:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v2
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
    - name: Run PHP_CodeSniffer
      run: vendor/bin/phpcs
```

### GitLab CI

Add the following to your `.gitlab-ci.yml` file:

```yaml
phpcs:
  stage: test
  image: php:8.2
  script:
    - composer install
    - vendor/bin/phpcs
```

## Next Steps

Now that you know how to use the ArtisanPack UI Code Standards package, you might want to learn more about:

- [Custom Sniffs](../sniffs/sniffs) included in the package
- How to [customize the standard](../customization/customization) for your specific needs