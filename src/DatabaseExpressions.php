<?php

declare(strict_types=1);

namespace PhilipRehberger\DbExpressions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Database-agnostic SQL expression helper.
 *
 * Provides static methods that return raw SQL strings for date formatting,
 * date extraction, and date difference calculations. All methods return
 * driver-appropriate SQL for SQLite and MySQL/MariaDB.
 *
 * SECURITY: All $column parameters MUST be trusted column names, not user input.
 * A regex guard rejects anything that doesn't match [a-zA-Z0-9_.]+.
 */
class DatabaseExpressions
{
    /**
     * Get the current database driver name.
     */
    public static function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    /**
     * Whether the current driver is SQLite.
     */
    public static function isSqlite(): bool
    {
        return static::driver() === 'sqlite';
    }

    // ---------------------------------------------------------------
    // Date Formatting — returns formatted date strings for GROUP BY
    // ---------------------------------------------------------------

    /**
     * Format a datetime column to hourly buckets: 'YYYY-MM-DD HH:00:00'.
     */
    public static function dateTruncHour(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "strftime('%Y-%m-%d %H:00:00', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m-%d %H:00:00')";
    }

    /**
     * Format a datetime column to daily buckets: 'YYYY-MM-DD'.
     */
    public static function dateTruncDay(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "strftime('%Y-%m-%d', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m-%d')";
    }

    /**
     * Format a datetime column to weekly buckets: 'YYYY-WW'.
     *
     * Note: SQLite uses %W (Monday as first day, 00-53) while MySQL uses %u
     * (Monday as first day, 01-53). Week numbering may differ by one between drivers.
     */
    public static function dateTruncWeek(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "strftime('%Y-%W', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%u')";
    }

    /**
     * Format a datetime column to monthly buckets: 'YYYY-MM'.
     */
    public static function dateTruncMonth(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    /**
     * Format a datetime column to yearly buckets: 'YYYY'.
     */
    public static function dateTruncYear(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "strftime('%Y', {$column})"
            : "DATE_FORMAT({$column}, '%Y')";
    }

    /**
     * General-purpose date format expression.
     *
     * Accepts a groupBy period ('hour', 'day', 'week', 'month', 'year')
     * and returns the appropriate truncation expression.
     */
    public static function dateFormat(string $column, string $groupBy): string
    {
        return match ($groupBy) {
            'hour' => static::dateTruncHour($column),
            'day' => static::dateTruncDay($column),
            'week' => static::dateTruncWeek($column),
            'month' => static::dateTruncMonth($column),
            'year' => static::dateTruncYear($column),
            default => throw new InvalidArgumentException(
                "Unknown date format period: '{$groupBy}'. Valid periods are: hour, day, week, month, year."
            ),
        };
    }

    // ---------------------------------------------------------------
    // Date Part Extraction — returns integer values
    // ---------------------------------------------------------------

    /**
     * Extract the hour (0-23) from a datetime column.
     */
    public static function extractHour(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "CAST(strftime('%H', {$column}) AS INTEGER)"
            : "HOUR({$column})";
    }

    /**
     * Extract the day of month (1-31) from a datetime column.
     */
    public static function extractDay(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "CAST(strftime('%d', {$column}) AS INTEGER)"
            : "DAY({$column})";
    }

    /**
     * Extract the week number from a datetime column.
     *
     * Note: SQLite strftime('%W') counts weeks from 00 (Monday-based), while
     * MySQL WEEK() defaults to mode 0 (Sunday-based, 0-53). Results may differ
     * between drivers for the same date.
     */
    public static function extractWeek(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "CAST(strftime('%W', {$column}) AS INTEGER)"
            : "WEEK({$column})";
    }

    /**
     * Extract the month (1-12) from a datetime column.
     */
    public static function extractMonth(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    /**
     * Extract the year from a datetime column.
     */
    public static function extractYear(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "CAST(strftime('%Y', {$column}) AS INTEGER)"
            : "YEAR({$column})";
    }

    /**
     * Extract the quarter (1-4) from a datetime column.
     */
    public static function extractQuarter(string $column): string
    {
        static::guardColumn($column);

        return static::isSqlite()
            ? "((CAST(strftime('%m', {$column}) AS INTEGER) - 1) / 3) + 1"
            : "QUARTER({$column})";
    }

    // ---------------------------------------------------------------
    // Date Differences
    // ---------------------------------------------------------------

    /**
     * Calculate the difference in days between two datetime columns.
     *
     * Returns column1 - column2 in days (positive if column1 is later).
     */
    public static function dateDiffDays(string $column1, string $column2): string
    {
        static::guardColumn($column1);
        static::guardColumn($column2);

        return static::isSqlite()
            ? "CAST((julianday({$column1}) - julianday({$column2})) AS INTEGER)"
            : "DATEDIFF({$column1}, {$column2})";
    }

    /**
     * Calculate the difference in hours between two datetime columns.
     *
     * Returns column1 - column2 in hours (positive if column1 is later).
     * Note: MySQL TIMESTAMPDIFF takes (unit, from, to) so arguments are swapped.
     */
    public static function dateDiffHours(string $column1, string $column2): string
    {
        static::guardColumn($column1);
        static::guardColumn($column2);

        return static::isSqlite()
            ? "(julianday({$column1}) - julianday({$column2})) * 24"
            : "TIMESTAMPDIFF(HOUR, {$column2}, {$column1})";
    }

    // ---------------------------------------------------------------
    // Column Name Guard
    // ---------------------------------------------------------------

    /**
     * Validate that a column name is safe for use in raw SQL.
     *
     * @throws InvalidArgumentException if the column name contains unsafe characters
     */
    private static function guardColumn(string $column): void
    {
        if (! preg_match('/^[a-zA-Z0-9_.]+$/', $column)) {
            throw new InvalidArgumentException(
                "Invalid column name: '{$column}'. Column names must match [a-zA-Z0-9_.]+"
            );
        }
    }
}
