# Laravel DB Expressions

[![Tests](https://github.com/philiprehberger/laravel-db-expressions/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/laravel-db-expressions/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/laravel-db-expressions.svg)](https://packagist.org/packages/philiprehberger/laravel-db-expressions)
[![License](https://img.shields.io/github/license/philiprehberger/laravel-db-expressions)](LICENSE)

Database-agnostic SQL expression helper for Laravel. Provides static methods that return raw SQL strings for date truncation, date part extraction, and date difference calculations — automatically handling the syntax differences between **SQLite** and **MySQL/MariaDB**.

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require philiprehberger/laravel-db-expressions
```

The service provider and facade are registered automatically via Laravel's package discovery.

## Usage

All methods are static and return plain SQL strings suitable for use in Eloquent's `selectRaw`, `groupByRaw`, `orderByRaw`, and `whereRaw` calls.

### Date Truncation (for GROUP BY buckets)

Group records into time buckets using the `dateTrunc*` methods or the general-purpose `dateFormat` dispatcher.

```php
use PhilipRehberger\DbExpressions\DatabaseExpressions;

// Hourly buckets: '2026-03-05 14:00:00'
$expr = DatabaseExpressions::dateTruncHour('created_at');

// Daily buckets: '2026-03-05'
$expr = DatabaseExpressions::dateTruncDay('created_at');

// Weekly buckets: '2026-09'
$expr = DatabaseExpressions::dateTruncWeek('created_at');

// Monthly buckets: '2026-03'
$expr = DatabaseExpressions::dateTruncMonth('created_at');

// Yearly buckets: '2026'
$expr = DatabaseExpressions::dateTruncYear('created_at');

// General dispatcher — throws `InvalidArgumentException` for unknown periods
$expr = DatabaseExpressions::dateFormat('created_at', 'week');
```

Real Eloquent query example:

```php
use PhilipRehberger\DbExpressions\DatabaseExpressions;

$period = 'month'; // from request, e.g. hour|day|week|month|year

$results = Invoice::query()
    ->selectRaw(DatabaseExpressions::dateFormat('created_at', $period) . ' as period, SUM(total) as revenue')
    ->groupByRaw(DatabaseExpressions::dateFormat('created_at', $period))
    ->orderByRaw(DatabaseExpressions::dateFormat('created_at', $period))
    ->get();
```

### Date Part Extraction (integer values)

Extract individual date components as integers.

```php
use PhilipRehberger\DbExpressions\DatabaseExpressions;

// Hour of day: 0–23
$expr = DatabaseExpressions::extractHour('created_at');

// Day of month: 1–31
$expr = DatabaseExpressions::extractDay('created_at');

// Week number: 0–53
$expr = DatabaseExpressions::extractWeek('created_at');

// Month: 1–12
$expr = DatabaseExpressions::extractMonth('created_at');

// Year: e.g. 2026
$expr = DatabaseExpressions::extractYear('created_at');

// Quarter: 1–4
$expr = DatabaseExpressions::extractQuarter('created_at');
```

Real Eloquent query example:

```php
// Find which hour of the day has the most activity
$results = ApiUsageLog::query()
    ->selectRaw(DatabaseExpressions::extractHour('created_at') . ' as hour, COUNT(*) as hits')
    ->groupByRaw(DatabaseExpressions::extractHour('created_at'))
    ->orderByRaw(DatabaseExpressions::extractHour('created_at'))
    ->get();
```

### Date Differences

Calculate the difference between two datetime columns.

```php
use PhilipRehberger\DbExpressions\DatabaseExpressions;

// Difference in whole days (column1 - column2)
$expr = DatabaseExpressions::dateDiffDays('completed_at', 'created_at');

// Difference in hours (column1 - column2)
$expr = DatabaseExpressions::dateDiffHours('completed_at', 'created_at');
```

Real Eloquent query example:

```php
// Average project duration in days
$avg = Project::query()
    ->whereNotNull('completed_at')
    ->selectRaw('AVG(' . DatabaseExpressions::dateDiffDays('completed_at', 'created_at') . ') as avg_days')
    ->value('avg_days');
```

### Facade

You can also use the `DbExpressions` facade:

```php
use PhilipRehberger\DbExpressions\Facades\DbExpressions;

$expr = DbExpressions::dateTruncMonth('created_at');
$expr = DbExpressions::extractQuarter('invoiced_at');
$expr = DbExpressions::dateDiffDays('due_at', 'created_at');
```

### Driver Detection

```php
use PhilipRehberger\DbExpressions\DatabaseExpressions;

$driver = DatabaseExpressions::driver();   // 'sqlite', 'mysql', etc.
$isSqlite = DatabaseExpressions::isSqlite(); // bool
```

## Multi-Driver Support

| Method | SQLite | MySQL / MariaDB |
|---|---|---|
| `dateTruncHour` | `strftime('%Y-%m-%d %H:00:00', col)` | `DATE_FORMAT(col, '%Y-%m-%d %H:00:00')` |
| `dateTruncDay` | `strftime('%Y-%m-%d', col)` | `DATE_FORMAT(col, '%Y-%m-%d')` |
| `dateTruncWeek` | `strftime('%Y-%W', col)` | `DATE_FORMAT(col, '%Y-%u')` |
| `dateTruncMonth` | `strftime('%Y-%m', col)` | `DATE_FORMAT(col, '%Y-%m')` |
| `dateTruncYear` | `strftime('%Y', col)` | `DATE_FORMAT(col, '%Y')` |
| `extractHour` | `CAST(strftime('%H', col) AS INTEGER)` | `HOUR(col)` |
| `extractDay` | `CAST(strftime('%d', col) AS INTEGER)` | `DAY(col)` |
| `extractWeek` | `CAST(strftime('%W', col) AS INTEGER)` | `WEEK(col)` |
| `extractMonth` | `CAST(strftime('%m', col) AS INTEGER)` | `MONTH(col)` |
| `extractYear` | `CAST(strftime('%Y', col) AS INTEGER)` | `YEAR(col)` |
| `extractQuarter` | `((CAST(strftime('%m', col) AS INTEGER) - 1) / 3) + 1` | `QUARTER(col)` |
| `dateDiffDays` | `CAST((julianday(c1) - julianday(c2)) AS INTEGER)` | `DATEDIFF(c1, c2)` |
| `dateDiffHours` | `(julianday(c1) - julianday(c2)) * 24` | `TIMESTAMPDIFF(HOUR, c2, c1)` |

## Security

All `$column` parameters are validated against the pattern `[a-zA-Z0-9_.]+` before being interpolated into SQL. Passing an invalid column name (e.g. user-supplied input) throws an `InvalidArgumentException`. Never pass raw user input as a column name.

## Known Limitations

### Week Number Semantics

The `dateTruncWeek()` and `extractWeek()` methods produce slightly different week numbers between SQLite and MySQL:

| Driver | `dateTruncWeek` format | `extractWeek` function | Week start |
|--------|----------------------|----------------------|------------|
| SQLite | `strftime('%W')` — Monday-based, 00–53 | `strftime('%W')` — Monday-based, 00–53 | Monday |
| MySQL  | `DATE_FORMAT('%u')` — Monday-based, 01–53 | `WEEK()` — mode 0, Sunday-based, 0–53 | Varies |

If exact cross-driver parity is required for week numbers, consider using `dateTruncDay()` and computing week buckets in application code.

### dateFormat() Throws on Invalid Periods

The `dateFormat()` dispatcher throws an `InvalidArgumentException` if the period is not one of: `hour`, `day`, `week`, `month`, `year`. Validate user input before passing it to this method.

## API

| Method | Description |
|--------|-------------|
| `DatabaseExpressions::dateTruncHour(string $column): string` | SQL expression for hourly time bucket |
| `DatabaseExpressions::dateTruncDay(string $column): string` | SQL expression for daily time bucket |
| `DatabaseExpressions::dateTruncWeek(string $column): string` | SQL expression for weekly time bucket |
| `DatabaseExpressions::dateTruncMonth(string $column): string` | SQL expression for monthly time bucket |
| `DatabaseExpressions::dateTruncYear(string $column): string` | SQL expression for yearly time bucket |
| `DatabaseExpressions::dateFormat(string $column, string $period): string` | General dispatcher for date truncation; throws on invalid period |
| `DatabaseExpressions::extractHour(string $column): string` | Extract hour of day as integer (0–23) |
| `DatabaseExpressions::extractDay(string $column): string` | Extract day of month as integer (1–31) |
| `DatabaseExpressions::extractWeek(string $column): string` | Extract week number as integer (0–53) |
| `DatabaseExpressions::extractMonth(string $column): string` | Extract month as integer (1–12) |
| `DatabaseExpressions::extractYear(string $column): string` | Extract year as integer |
| `DatabaseExpressions::extractQuarter(string $column): string` | Extract quarter as integer (1–4) |
| `DatabaseExpressions::dateDiffDays(string $col1, string $col2): string` | Difference between two date columns in whole days |
| `DatabaseExpressions::dateDiffHours(string $col1, string $col2): string` | Difference between two date columns in hours |
| `DatabaseExpressions::driver(): string` | Return the current DB driver name |
| `DatabaseExpressions::isSqlite(): bool` | Whether the current connection is SQLite |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## License

MIT

