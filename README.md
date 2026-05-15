# Customized CodeIgniter 3

## Important Change
This CodeIgniter 3 can run on PHP 8.3 so we have modified the **system files**, but you will most likely cannot run it in PHP 8.4. It is not fixable unless you modify the system files heavily as it will change it algorithm of its session creation, so we leave it as is.
What we did:
- Add `#[\AllowDynamicProperties]` on most **system files** that are affected in PHP 8.3.
- Modify deprecated error level exceptions so it will not used in PHP 8.0.
- Always run `composer update` for the first time you setup this app.

## Features

- `application/config/config.php` is modified to automatically use logging, load composer, and dynamic base URL.
- `application/config/database.php` and `application/config/config.php` is moved to development folder.
- `application/config/autoload.php` is modified to autoload most mandatory libraries.
- `application/config/migration.php` is modified to enable migrations.
- `application/config/constants.php` is modified to define VIEW_PATH, SCRIPT_PATH, UPLOAD_PATH and ASSETS_PATH.
- We have created `application/config/development/app.php` so you can have custom config without modifying the default config files. Important! You need to create a copy of it for production.
- `readme.rst` and `license.txt` is deleted, but we created `README.md` and `robots.txt` instead.
- `.htaccess` is created to disable direct access to `.git` folder, `upload` and `assets` folder, also most files in root dir. It also has force HTTPS, but is disabled by default, you can enable it by removing the comment. You can also set ENVIRONMENT (production/development/testing) in `.htaccess`.
- `.gitignore` is modified to handle `composer.lock`, `vendor` folder and `upload` folder.
- `composer.json` is moved inside `application` folder, and modified to install `symfony/var-dumper` (dd() function), `virdiggg/seeder-ci3` (artisan-like function for CI3), `virdiggg/log-parser-ci3` (view log in JSON) and `virdiggg/merge-files` (merge files into a single PDF).
- Example of manifest.json
- Have log parser endpoint with controller `api/Storage.php`
- Have helpers `arr`, `str`, `permission`, and `encrypt` which have many useful functions.
- `upload`, `assets`, `application/storage` and `application/migrations` folder is automatically created.
- Have custom HTTP 500 handler `application/core/MY_Exceptions.php`, because it's default handler is shit even on production mode. HTML file is `applications/views/errors/html/error_500.php`
- Helper `encrypt` & `permission` have integrated slug function with controller `api/Storage.php`
```php
$this->load->helper('encrypt');
$slug = parseSlug(encrypt(APPPATH.'storage/filename.pdf'));

echo "<img src=\"".base_url($slug)."\" alt=\"img\" />";
```
- You can use custom benchmark in `application/config/development/app.php`, just switch `$config['using_benchmark']` value to `true`.
```diff
-$config['using_benchmark']         = false; // true or false
+$config['using_benchmark']         = true; // true or false
```
- Have custom logging library `Logger.php` in `libraries` folder. Same as the default `log_message()` but you can define the log file name. To use:
```php
$this->load->library('Logger');
$this->logger->setLogPath('queries'); // File name
$message = json_encode(['COBA', 'TES']);
$this->logger->write_log('error', $message);
```
- Have library to merge files into a single PDF. See controllers/App.php for example.
- Have library to export to .xlsx. See controllers/App.php for example.
- Have trait soft delete `application/traits/SoftDelete.php` if you want to use soft delete query.
- Have `README_SOFTDELETE.md` which explains how to use soft delete trait.
- `application/config/database.php` and `application/config/config.php` is moved to development folder.
- Have query profiler `Queries.php` in `libraries` folder. Disabled by default, enable it in `application/config/hooks.php`. To use:
```diff
/**
 * Profilling queries
 * 
 * @see application/hooks/Queries.php
 * @see application/logs/queries-log-YYYY-MM-DD.php
 */
-$hook['post_system'] = [
-    'class'    => 'Queries',
-    'function' => 'logging',
-    'filename' => 'Queries.php',
-    'filepath' => 'hooks',
-];
+$hook['post_system'] = [
+    'class'    => 'Queries',
+    'function' => 'logging',
+    'filename' => 'Queries.php',
+    'filepath' => 'hooks',
+];
```