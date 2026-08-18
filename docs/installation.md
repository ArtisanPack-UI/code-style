---
title: Installation Guide
---

# Installation

This guide will walk you through the process of installing the ArtisanPack UI Code Standards package in your PHP project.

## Requirements

Before installing the package, make sure your environment meets the following requirements:

- PHP 8.2 or higher
- Composer

## Installation Steps

### 1. Install via Composer

You can install the ArtisanPack UI Code Standards package by running the following Composer command in your project directory:

```bash
composer require artisanpack-ui/code-style --dev
```

This will add the package as a development dependency in your project.

### 2. Verify Installation

After installation, you can verify that the package was installed correctly by checking your `composer.json` file. You should see the package listed in the `require-dev` section:

```json
{
    "require-dev": {
        "artisanpack-ui/code-style": "^1.0"
    }
}
```

You can also check that the package files are present in your `vendor` directory:

```bash
ls -la vendor/artisanpack-ui/code-style
```

## Next Steps

Once you have installed the package, you can proceed to [configure it for your project](Usage-Usage).

If you encounter any issues during installation, please check the [troubleshooting section](Troubleshooting) or [open an issue](https://gitlab.com/jacob-martella-web-design/artisanpack-ui/artisanpack-ui-code-style/-/issues) on our GitLab repository.
