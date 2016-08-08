# Changelog

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
