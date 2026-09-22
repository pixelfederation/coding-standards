# Changelog

All notable changes from version 4.0.0 onward are documented in this file.

## 6.2.0

### Added

- Added a project `EnumCaseName` sniff that requires strict PascalCase enum case names (for example `case OneTwo;`
  instead of `case ONE_TWO;`).

## 6.1.0

### Added

- Added a project `AlphabeticallySortedByKeys` sniff that extends Slevomat's sniff of the same name with an
  `ignoredParentKeys` property, so multi-line arrays that are the direct value of a configured parent key (for
  example `choices`) can keep a meaningful order instead of an alphabetical one. The containing array is still
  checked.

### Changed

- Excluded the superfluous trait name, superfluous abstract class prefix, alphabetical array key order, and function
  length checks from `tests/` directories.

## 6.0.0

### Added

- Added PHP 8.4 and PHP 8.5 GrumPHP test suites and a GitHub Actions workflow that tests against the latest allowed
  dependency versions.
- Added the `ClassKeywordOrder`, `ReadonlyClass`, `TraitUseOrder`, `CatchExceptionsOrder`, and
  `ThrowsAnnotationsOrder` Slevomat rules.
- Added a project `RequireAbstractOrFinal` sniff that accepts native `final`, `abstract`, and the `@final`
  annotation.
- Added functional coverage for forbidden annotations, abstract or final classes, whitespace fixes, and XML schema
  validation.

### Changed

- Raised the minimum PHP version to 8.4.
- Updated PHP_CodeSniffer to 4.0.4, Slevomat Coding Standard to 8.31.1, and the development dependencies to their
  current supported versions.
- Made PHP 8.4 the base ruleset and made PHP 8.5 extend it.
- Limited forbidden annotations to `@author`, `@copyright`, and `@license`; `@throws` remains allowed.
- Improved the XML linter to validate multi-namespace documents through their root schema and to restore libxml
  error handling after exceptions.
- Prevented functional PHPCS tests from modifying source fixtures.

### Removed

- Removed PHP 8.3 rulesets and runtime support.
- Removed the PHP Mess Detector dependency and the `phpmd_extended` GrumPHP task.

## 5.5.1 - 2026-02-03

### Fixed

- Fixed XML linting and task option handling.
- Improved compatibility of custom GrumPHP tasks with current GrumPHP APIs.

## 5.5.0 - 2026-01-07

### Added

- Added the `phpstan_extended` task with chunked PHPStan execution.
- Added the `xmllint_extended` task with strict schema validation.

## 5.4.0 - 2025-12-15

### Added

- Added PHP 8.5 rulesets and a PHP 8.5 development container.

## 5.3.1 - 2025-12-11

### Added

- Added explicit end-of-file newline enforcement.
- Extended functional test helpers for PHPCS fix verification.

## 5.3.0 - 2025-12-09

### Added

- Added stricter whitespace rules.
- Added functional tests for PHPCS fixes.

## 5.2.0 - 2025-11-14

### Added

- Added the chunked `phpmd_extended` GrumPHP task.

## 5.1.1 - 2025-11-14

### Added

- Added enforcement for whitespace on otherwise empty lines.

## 5.1.0 - 2025-11-13

### Added

- Added GrumPHP integration and custom tasks for Composer installation checks and Doctrine schema validation.
- Added project configuration for PHP-CS-Fixer, PHPCS, and PHPStan.

## 5.0.0 - 2025-10-22

### Changed

- Reworked the version-specific ruleset hierarchy.
- Updated the development environment and project tooling.

### Removed

- Removed obsolete PHP 8.1 and PHP 8.2 rulesets.
- Removed sniffs deprecated by newer PHP_CodeSniffer and Slevomat Coding Standard releases.

## 4.1.1 - 2025-02-25

### Fixed

- Fixed the installed path configuration for both local development and Composer-installed usage.

## 4.1.0 - 2025-02-24

### Added

- Added PHP 8.4 support and version-specific PHP 8.4 rulesets.
- Added the Pixel Federation custom sniff standard and the switch-statement restriction.

### Changed

- Updated package dependencies and reorganized example files.
- Replaced the generic ruleset with PHP-version-specific rulesets.

## 4.0.0 - 2024-11-26

### Added

- Added PHP 8.3 support.
