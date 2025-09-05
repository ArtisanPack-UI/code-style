---
title: ArtisanPack UI Code Standards Documentation
---

# ArtisanPack UI Code Standards Documentation

Welcome to the documentation for the ArtisanPack UI Code Standards package. This documentation will help you understand how to use the package, configure it for your projects, and customize it to meet your specific needs.

## Overview

ArtisanPack UI Code Standards is a custom PHP code style standard based on PHPStorm settings. This package provides custom sniffs for PHP_CodeSniffer that enforce consistent code style across your PHP projects.

## Table of Contents

### Getting Started
- [Installation](installation) - How to install the package
- [Troubleshooting](troubleshooting) - Common issues and solutions

### Usage & Configuration
- [Usage Guide](usage/usage) - Basic usage instructions
- [Customization](customization/customization) - How to customize the standard

### Sniffs Reference
- [All Custom Sniffs](sniffs/sniffs) - Complete overview of all sniffs
- [Security Sniffs](sniffs/security-sniffs) - Security-focused sniffs documentation

### Contributing
- [Contributing Guidelines](contributing/contributing) - How to contribute to the project
- [AI Guidelines](contributing/ai-guidelines) - Guidelines for AI-assisted development

## Quick Start

### Installation

```bash
composer require artisanpack-ui/code-style --dev
```

### Basic Configuration

Create a `phpcs.xml` file in your project root:

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

```bash
./vendor/bin/phpcs --standard=ArtisanPackUIStandard .
```

Or if you've set up a custom `phpcs.xml` file:

```bash
./vendor/bin/phpcs
```

For more detailed information, please refer to the specific documentation sections linked in the table of contents.