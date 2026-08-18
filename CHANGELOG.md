# ArtisanPack UI Code Style

## [1.2.0] - 2026-08-17
### Added
* Support for PHP_CodeSniffer 4.x. The `squizlabs/php_codesniffer` constraint is now `^3.7 || ^4.0`, unblocking downstream projects that want to upgrade to PHPCS 4.x.

### Fixed
* Replaced the removed `T_ARRAY_HINT` token with `T_ARRAY` in `TypeDeclarationSniff` so the custom sniffs run without a fatal error under PHPCS 4.x while remaining compatible with 3.x.

### Changed
* `tests/TestCase.php` now extends PHPUnit's base test case instead of Laravel's, and the feature suite runs the standard against a sample file to verify sniffs fire.

## [1.1.0] - 2025-11-22
### Added
* Added support for Laravel Boost.

### Changed
* Improved release packaging by excluding unnecessary development files via .gitattributes.
* Enhanced .gitignore to exclude macOS system files (.DS_Store).

## [1.0.5] - 2025-06-11
### Fixed
* Removed the line length rule check.
* Updated TypeDeclarationSniff to skip property type checks for classes inside Models directories.

## [1.0.4] - 2025-06-09
### Fixed
* Updated the composer.json file to remove references to Laravel and to add the package descriptions.

## [1.0.3] - 2025-06-08
### Fixed
* Finally fixed issue with line length error incorrectly being displayed.

## [1.0.2] - 2025-06-08
### Fixed
* Fixed issue with line length error incorrectly being displayed.

## [1.0.1] - 2025-06-08
### Fixed
* Fixed a problem with requiring declaring types on Model properties in Laravel when that causes problems.

## [1.0.0] - 2025-06-08
* Initial release
