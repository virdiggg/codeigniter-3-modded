# CodeIgniter 3 Modded

A customized and modernized distribution of CodeIgniter 3 designed for long-term maintainability, PHP 8.3 compatibility, and improved developer experience in legacy enterprise applications.

This project extends the original CodeIgniter 3 framework with practical quality-of-life improvements, security enhancements, development tooling, logging utilities, migration support, and production-oriented optimizations while preserving the lightweight nature and architectural simplicity of CI3.

---

## Overview

CodeIgniter 3 remains widely used in many enterprise and internal systems due to its simplicity and low operational overhead.

However, the framework is increasingly difficult to maintain on modern PHP versions and lacks many conveniences found in newer ecosystems.

This distribution was created to address those limitations by introducing:

- PHP 8.3 compatibility adjustments
- Better project structure conventions
- Migration tooling
- Enhanced logging and debugging
- Security-focused defaults
- Resource generation utilities
- Query profiling
- PDF and XLSX helper integrations
- Cleaner environment separation

without fundamentally changing how CodeIgniter 3 works.

---

# PHP Compatibility

## PHP 8.3 Support

This distribution includes modifications to core CodeIgniter 3 system files to improve compatibility with PHP 8.3.

### What Was Modified

- Added `#[\AllowDynamicProperties]` to affected system classes
- Updated deprecated error handling logic for PHP 8+
- Adjusted compatibility behavior for modern PHP runtime changes

---

## Important Limitation: PHP 8.4

This project is currently **not compatible with PHP 8.4**.

The incompatibility primarily originates from internal session handling behavior within CodeIgniter 3 itself.

Supporting PHP 8.4 would require substantial rewrites to CI3 core internals and session algorithms, which would significantly diverge from the original framework architecture.

For stability and maintainability reasons, this project intentionally preserves the existing CI3 architecture instead of heavily rewriting the framework core.

---

# Initial Setup

After cloning the project:

```bash
composer update
```

This is required during the first setup to install bundled dependencies.

---

# Included Improvements

## Cleaner URL Structure

- `index.php` is removed from URLs by default

---

## Dynamic Environment Configuration

Configuration files are restructured for cleaner environment separation.

### Moved Into Development Environment

- `application/config/config.php`
- `application/config/database.php`

---

## Automatic Composer Integration

Composer autoloading is enabled by default.

---

## Dynamic Base URL Detection

`config.php` is modified to support automatic base URL generation.

---

## Enhanced Autoload Configuration

`autoload.php` is preconfigured with commonly required helpers and libraries.

---

## Migration Support Enabled

Migration support is enabled by default through:

```php
application/config/migration.php
```

---

## Extended Constants

Additional application path constants are included:

- `VIEW_PATH`
- `SCRIPT_PATH`
- `UPLOAD_PATH`
- `ASSETS_PATH`

---

# Environment-Based App Configuration

Additional configuration layer:

```bash
application/config/development/app.php
```

This file allows application-specific settings without modifying core framework configs.

For production deployments, create a separate production configuration copy.

---

# Security Enhancements

## Hardened `.htaccess`

The included `.htaccess` contains protections for:

- `.git` directory access
- Upload directory access
- Sensitive root files
- Direct asset exposure

Optional features:

- Force HTTPS
- Environment switching
- Request restrictions

---

# Built-In Utilities

## Seeder & Migration Toolkit

Integrated:

```text
virdiggg/seeder-ci3
```

Provides:

- Migration generation
- Seeder generation
- Resource scaffolding
- Artisan-like developer workflows

---

## Query Profiling System

Includes query profiler hook:

```text
application/hooks/Queries.php
```

Features:

- SQL query logging
- Execution timing
- Query debugging
- Daily log separation

### Enable Query Profiler

Edit:

```php
application/config/hooks.php
```

Enable:

```php
$hook['post_system'] = [
    'class'    => 'Queries',
    'function' => 'logging',
    'filename' => 'Queries.php',
    'filepath' => 'hooks',
];
```

---

## Enhanced Logging System

Custom logging library:

```text
application/libraries/Logger.php
```

Allows custom log channels/files.

Example:

```php
$this->load->library('Logger');

$this->logger->setLogPath('queries');

$message = json_encode(['COBA', 'TES']);

$this->logger->write_log('error', $message);
```

---

## Better HTTP 500 Handling

Default CI3 production error handling is replaced with a cleaner custom implementation.

Files:

```text
application/core/MY_Exceptions.php
application/views/errors/html/error_500.php
```

---

# Included Helper Libraries

Additional helpers included:

- `arr`
- `str`
- `permission`
- `encrypt`

These provide commonly used utility functions missing from default CI3.

---

## Slug-Based Secure File Access

Integrated secure slug generation for storage endpoints.

Example:

```php
$this->load->helper('encrypt');

$slug = parseSlug(
    encrypt(APPPATH.'storage/filename.pdf')
);

echo "<img src=\"".base_url($slug)."\" alt=\"img\" />";
```

---

# Development Utilities

## Custom Benchmarking

Benchmarking can be enabled through:

```php
$config['using_benchmark'] = true;
```

Configuration file:

```text
application/config/development/app.php
```

---

## Automatic Directory Initialization

The following directories are automatically generated:

- `upload`
- `assets`
- `application/storage`
- `application/migrations`

---

# Built-In Document Utilities

## PDF Merge Library

Integrated:

```text
virdiggg/merge-files
```

Supports:

- PDF merging
- Office document merging
- Image-to-PDF workflows

Example usage available in:

```text
controllers/App.php
```

---

## XLSX Export Utility

Built-in XLSX export helper included.

Example usage available in:

```text
controllers/App.php
```

---

# Soft Delete Trait

Includes reusable soft delete trait:

```text
application/traits/SoftDelete.php
```

Documentation:

```text
README_SOFTDELETE.md
```

---

# Composer Packages Included

This distribution includes several development-focused packages by default:

- `symfony/var-dumper`
- `virdiggg/seeder-ci3`
- `virdiggg/log-parser-ci3`
- `virdiggg/merge-files`

---

# Log Parser Endpoint

Built-in log parser endpoint included through:

```text
api/Storage.php
```

Useful for:

- JSON log viewing
- Internal monitoring
- Debugging dashboards

---

# Recommended Use Cases

This distribution is especially suitable for:

- Legacy enterprise applications
- Internal operational systems
- Long-term maintenance projects
- PostgreSQL-backed CI3 applications
- Rapid backend development
- Teams modernizing old CI3 codebases
- Self-hosted business systems

---

# Philosophy

This project does not attempt to transform CodeIgniter 3 into Laravel.

Instead, the goal is to:

- Preserve CI3 simplicity
- Improve maintainability
- Add practical modern tooling
- Reduce repetitive setup work
- Improve operational visibility
- Extend the usable lifespan of legacy applications

while keeping the framework lightweight and familiar for existing CI3 developers.
