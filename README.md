# CodeIgniter 3 Modded

A customized and modernized distribution of CodeIgniter 3 designed for long-term maintainability, PHP 8.5 compatibility, and improved developer experience in legacy enterprise applications.

This project extends the original CodeIgniter 3 framework with practical quality-of-life improvements, security enhancements, development tooling, logging utilities, migration support, blade-like templating, pusher and redis support, and production-oriented optimizations while preserving the lightweight nature and architectural simplicity of CI3.

---

## Overview

CodeIgniter 3 remains widely used in many enterprise and internal systems due to its simplicity and low operational overhead.

However, the framework is increasingly difficult to maintain on modern PHP versions and lacks many conveniences found in newer ecosystems.

This distribution was created to address those limitations by introducing:

- PHP 8.5 compatibility adjustments
- Better project structure conventions
- Migration tooling
- Enhanced logging and debugging
- Blade-like templating
- Security-focused defaults
- Resource generation utilities
- Query profiling
- PDF and XLSX helper integrations
- Async PostgreSQL query support
- Cleaner environment separation
- Real-time event broadcasting through Pusher
- Redis integration for caching and temporary data storage

without fundamentally changing how CodeIgniter 3 works.

---

# PHP Compatibility

## PHP 8.5 Support

This distribution includes modifications to core CodeIgniter 3 system files to improve compatibility with PHP 8.5.

### What Was Modified

- Added `#[\AllowDynamicProperties]` to affected system classes
- Updated deprecated error handling logic for PHP 8+
- Adjusted compatibility behavior for modern PHP runtime changes
- Adjusted how the core system make their session
- The bundled `pgasync` driver targets PHP 7.4–8.5 and depends only on `ext-pgsql`; it does not use any PHP 8-only syntax or functions.

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

Provides modern CLI tooling and Artisan-like workflows for CodeIgniter 3.

Features include:

- Migration generation
- Migration execution
- Migration rollback
- Seeder generation
- Seeder execution
- Batch seeding
- Model generation
- Controller generation
- CRUD scaffolding
- API resource scaffolding
- Environment-aware command execution
- CLI helper utilities
- Blade-like templating support

Read more:

- [Seeder CI3](https://github.com/virdiggg/seeder-ci3)
- [Seeder CI3 Usage Guide](https://github.com/virdiggg/seeder-ci3/blob/master/USAGE.md)
- [Seeder CI3 Upgrade Guide](https://github.com/virdiggg/seeder-ci3/blob/master/UPGRADE.md)

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
application/config/(ENVIRONMENT)/config.php
```

Enable:

```php
$config['enable_hooks'] = TRUE;
```

---

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

## Async PostgreSQL Driver

Includes a custom `pgasync` database driver for non-blocking, concurrent
PostgreSQL queries — a drop-in extension of CI3's stock `postgre` driver.

Enable it in `application/config/database.php`:

```php
$db['default']['dbdriver'] = 'pgasync';
$db['default']['pool_size'] = 4;
```

Existing code (`$this->db->query()`, Active Record, migrations) is unaffected. New opt-in methods for concurrent queries:

```php
$h = $this->db->query_async("SELECT ...");
$result = $this->db->await($h);
```

Read more:

- [Async PostgreSQL Driver Guide](./docs/ASYNCPG.md)

---

## Enhanced Logging System

Custom logging library:

```text
application/libraries/MY_Logger.php
```

Allows custom log channels/files.

Example:

```php
$this->load->library('MY_Logger', NULL, 'logger');

$this->logger->setFilenamePrefix('queries');

$message = json_encode(['COBA', 'TES']);

$this->logger->write_log('error', $message);
```

---

## Better HTTP 500 Handling

Default CI3 production error handling is replaced with a cleaner custom implementation as default CI3 500 exception still throw error code to view even in production ENVIRONMENT.

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
- `template`

These provide commonly used utility functions missing from default CI3.

# Realtime & Cache Integrations

## Pusher Integration

This distribution includes Pusher support through the official [Pusher PHP Server SDK](https://github.com/pusher/pusher-http-php).


### Configuration

Configure your Pusher credentials in:

```text
application/config/(ENVIRONMENT)/pusher.php
```

Example configuration:

```php
$config['pusher_app_id'] = '';
$config['pusher_key'] = '';
$config['pusher_secret'] = '';
$config['pusher_cluster'] = 'ap1';
$config['pusher_channel'] = '';
```

Provide the corresponding environment variables in your deployment environment.

### Usage

Pusher can be used to broadcast events to connected clients without requiring clients to continuously poll the server.

Example:

- Send pusher message
```php
$this->load->library('pusher');
$this->pusher->send('pusher_action', [
    'trx_id' => 123,
    'message' => 'Just simple message OK',
]);
```

- Receive push message
```javascript
<script type="text/javascript" src="<?= assets('js/pusher-7.0.6/pusher-with-encryption.min.js') ?>"></script>
<script type="text/javascript">
    const pusher = new Pusher("<?= $this->config->item('pusher_key') ?>", {
        cluster: "<?= $this->config->item('pusher_cluster') ?>",
        encrypted: true
    });
    const channel = pusher.subscribe("<?= $this->config->item('pusher_channel') ?>");
    channel.bind('pusher_action', function(data) {
        if (data) {
            // do something
            // showToast(data.message);
            // fetch...
        }
    });
</script>
```

Useful for:

- Real-time notifications
- Live dashboard updates
- Background task status updates
- Real-time application events

---

## Redis Integration

This distribution includes Redis support through Predis, providing a convenient interface for caching and other Redis operations, using [Predis](https://github.com/predis/predis).

### Configuration

Redis configuration is located at:

```text
application/config/redis.php
```

The connection supports environment-based configuration:

```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['redis_scheme'] = 'tcp'; // or tls
$config['redis_host'] = 'redis'; // or 127.0.0.1
$config['redis_port'] = 6379;
$config['redis_password'] = null;
$config['redis_ca_file']  = null; // tls only
```

Supported connection options:

- TCP connection
- Password authentication (optional)
- TLS connection (optional)
- CA certificate verification

### Usage

Load the Redis library:

```php
$this->load->library('RedisLib');
```

**Set a value**

```php
$this->redislib->set([
    'user:123' => json_encode([
        'name' => 'John Doe',
    ]),
], 3600);
```

The second argument specifies the expiration time in seconds.
Use `null` for persistent keys.

**Retrieve a value**

```php
$user = $this->redislib->get('user:123');

$user = $user ? json_decode($user) : null;
```

**Delete a key**

```php
$this->redislib->remove('user:123');
```

**Delete keys using a wildcard pattern**

```php
$this->redislib->removeWithWildCard('user:*');
```

**Select keys using a wildcard pattern**

```php
$this->redislib->getWithWildCard('user:*');
```

**Find all keys using a wildcard pattern**

```php
$this->redislib->keys('user:*');
```

**Flush Redis database**

```php
$this->redislib->flush();
```

**Test connection**

```php
$this->redislib->test();
```

### Available Methods

| Method | Description |
|---|---|
| `test()` | Test Redis connection |
| `keys($key)` | Retrieve all keys matching pattern |
| `get($key)` | Retrieve a value |
| `getWithWildCard($key)` | Select keys matching a pattern |
| `set($param, $timeout)` | Store one or multiple values |
| `remove($key)` | Delete one or multiple keys |
| `removeWithWildCard($key)` | Delete keys matching a pattern |
| `flush()` | Flush the Redis database |

Wildcard operations use Redis SCAN rather than KEYS.

### Common Use Cases

- Application caching
- Session-related data storage
- Temporary data storage
- Frequently accessed database results
- Cache invalidation

---

## Slug-Based Secure File Access

Integrated secure slug generation for storage endpoints.

Example:

```php
$this->load->helper('encrypt');

$slug = parseSlug(encrypt(APPPATH.'storage/filename.pdf'));

echo "<img src=\"".base_url($slug)."\" alt=\"img\" />";
```

---

# Development Utilities

## Custom Benchmarking

Default CI3's benchmark is replaced with custom benchmarking, can be enabled through:

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

## Blade-like templating

- Initial template file is `application/views/layouts/wrapper.php`, adjust as you need.
- Example view is `application/views/example.php`. This include template extending, push javascript and styles sention, HTML title, and so on.

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
controllers/Example.php
```

### Required PHP Extension

The `mbstring` extension is required.

### External Application Required

It is mandatory to install `ghostscript` when using this library.

### Why Ghostscript Is Required

Many PHP PDF libraries such as FPDI, FPDF, and mPDF have limitations when importing or merging PDFs generated with newer PDF specifications (commonly PDF 1.5+).

This library automatically preprocesses incompatible PDFs through Ghostscript to:

- Normalize PDF versions
- Improve merge compatibility
- Prevent parser/import failures
- Avoid dependency on commercial PDF parser addons

Ghostscript is therefore a mandatory dependency.

Read more:

- [merge-files](https://github.com/virdiggg/merge-files/blob/master/README.md)
- [ghostscript instalation](https://github.com/virdiggg/merge-files/blob/master/README.md#ghostscript-installation)

---

## XLSX Export Utility

Built-in XLSX export helper included.

Example usage available in:

```text
controllers/Example.php
```

---

# Soft Delete Trait

Includes reusable soft delete trait:

```text
application/traits/SoftDelete.php
```

Documentation:

- [Softdelete Trait](./docs/SOFTDELETE.md)

# Third-Party Attributions

This project includes or adapts third-party code and libraries.
Credits and original authorship are acknowledged below.

## PHP QR Code

The PHP QR Code library is a CodeIgniter port of the original PHP QR Code project.

- **Original project:** PHP QR Code
- **Original author:** [PHP QR Code Project](http://phpqrcode.sourceforge.net/)
- **CodeIgniter porting author:** Dwi Setiyadi
- **Contact:** dwi.setiyadi@gmail.com

The original library header and authorship notices are retained where applicable.

## PHP XLSX Generator

This project includes code based on the PHP XLSX Generator.

- **Class:** `PhpXlsxGenerator`
- **Original author:** CodexWorld
- **Contact:** codexworld.com@gmail.com

The original authorship notices are retained where applicable.

---

Third-party components remain subject to their respective original licenses and attribution requirements. Please refer to their original source distributions for licensing details.

---

# Composer Packages Included

This distribution includes several development-focused packages by default:

- `symfony/var-dumper`
- `virdiggg/seeder-ci3`
- `virdiggg/log-parser-ci3`
- `virdiggg/merge-files`
- `pusher/pusher-php-server`
- `predis/predis`

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
- PostgreSQL-backed CI3 applications, including concurrent/async query workloads
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
