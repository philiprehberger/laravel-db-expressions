# Changelog

All notable changes to `laravel-db-expressions` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.1.5] - 2026-03-23

### Fixed
- Standardize CHANGELOG preamble to use package name

## [1.1.4] - 2026-03-17

### Fixed
- Add phpstan.neon configuration for CI static analysis

## [1.1.3] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

## [1.1.2] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts
- Add Development section to README

## [1.1.1] - 2026-03-15

### Changed
- Add README badges

## [1.1.0] - 2026-03-12

### Changed
- `dateFormat()` now throws `InvalidArgumentException` on unknown periods instead of silently defaulting to month

### Added
- Facade delegation tests for `dateTruncMonth`, `extractQuarter`, and `dateDiffDays`
- Week format verification test
- Table-qualified column test for `dateDiffDays`
- All-valid-periods loop test for `dateFormat`
- "Known Limitations" section in README documenting SQLite/MySQL week numbering discrepancy
- Docblock annotations on `dateTruncWeek()` and `extractWeek()` about week numbering differences

### Fixed
- Documented SQLite/MySQL week numbering discrepancy

## [1.0.0] - 2026-03-05

### Added
- Initial release
- Date truncation methods (hour, day, week, month, year)
- Date part extraction methods (hour, day, week, month, year, quarter)
- Date difference methods (days, hours)
- Column name injection protection
- SQLite and MySQL/MariaDB support
