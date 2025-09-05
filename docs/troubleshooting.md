---
title: Troubleshooting Guide
---

# Troubleshooting Guide

This guide will help you resolve common issues you might encounter when using the ArtisanPack UI Code Standards package.

## Common Installation Issues

### Composer Installation Fails

**Problem:** Getting errors when running `composer require artisanpack-ui/code-style --dev`

**Solutions:**
- Ensure you have PHP 8.2 or higher installed
- Make sure Composer is up to date: `composer self-update`
- Clear Composer cache: `composer clear-cache`
- Try installing with verbose output: `composer require artisanpack-ui/code-style --dev -vvv`

### Package Not Found Error

**Problem:** Composer reports that the package cannot be found

**Solutions:**
- Verify the package name is correct: `artisanpack-ui/code-style`
- Check your Composer configuration allows dev packages
- Ensure your project's `minimum-stability` setting allows dev packages if needed

## Common Usage Issues

### Standard Not Found Error

**Problem:** Getting "ERROR: the 'ArtisanPackUIStandard' coding standard is not installed"

**Solutions:**
- Verify the package is installed: `composer show artisanpack-ui/code-style`
- Check if the standard is available: `./vendor/bin/phpcs -i`
- Try reinstalling the package: `composer remove artisanpack-ui/code-style --dev && composer require artisanpack-ui/code-style --dev`

### No Files to Check Error

**Problem:** PHP_CodeSniffer reports "No files to check"

**Solutions:**
- Verify the file paths in your `phpcs.xml` configuration exist
- Check that files have `.php` extension
- Ensure files are not excluded by your exclude patterns
- Try running with explicit file paths: `./vendor/bin/phpcs src/`

### Permission Denied Errors

**Problem:** Getting permission denied when running PHP_CodeSniffer

**Solutions:**
- Make sure the phpcs binary is executable: `chmod +x vendor/bin/phpcs`
- Check file permissions on your source files
- On some systems, you might need to run: `php vendor/bin/phpcs` instead of `./vendor/bin/phpcs`

## Configuration Issues

### Custom Rules Not Applied

**Problem:** Custom rules in `phpcs.xml` are being ignored

**Solutions:**
- Verify XML syntax is correct - use an XML validator
- Ensure rule names match exactly (case-sensitive)
- Check that your `phpcs.xml` file is in the project root
- Validate property names and values are correct

### IDE Integration Not Working

**Problem:** IDE is not showing code style violations

**Solutions:**
- Restart your IDE after installing the package
- Verify the path to phpcs binary in IDE settings
- Check that the correct coding standard is selected
- Ensure the IDE extension/plugin is up to date

## Performance Issues

### Slow Analysis

**Problem:** PHP_CodeSniffer runs very slowly on your project

**Solutions:**
- Add more exclude patterns for vendor directories and other non-essential files
- Use `.phpcs.xml.dist` for project defaults and `.phpcs.xml` for local overrides
- Consider using `--extensions=php` to limit file types checked
- Use `--ignore` patterns to exclude specific paths

### Memory Issues

**Problem:** Running out of memory during analysis

**Solutions:**
- Increase PHP memory limit: `php -d memory_limit=512M vendor/bin/phpcs`
- Exclude large files or directories that don't need checking
- Process files in smaller batches
- Use `--report=summary` for less detailed output

## Getting Help

If you're still experiencing issues after trying these solutions:

1. Check the [project issues](https://gitlab.com/jacob-martella-web-design/artisanpack-ui/artisanpack-ui-code-style/-/issues) to see if others have reported similar problems
2. [Create a new issue](https://gitlab.com/jacob-martella-web-design/artisanpack-ui/artisanpack-ui-code-style/-/issues/new) with:
   - Your PHP version
   - Your operating system
   - The exact error message
   - Your `phpcs.xml` configuration (if relevant)
   - Steps to reproduce the issue

## Useful Commands

Here are some helpful commands for debugging issues:

```bash
# Check PHP version
php --version

# Check installed packages
composer show artisanpack-ui/code-style

# List available coding standards
./vendor/bin/phpcs -i

# Run with verbose output
./vendor/bin/phpcs -v

# Check specific file with detailed output
./vendor/bin/phpcs --report=full path/to/file.php

# Validate phpcs.xml file
./vendor/bin/phpcs --config-show
```