<?php

declare(strict_types=1);

namespace PhilipRehberger\DbExpressions\Tests;

use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use PhilipRehberger\DbExpressions\DatabaseExpressions;
use PhilipRehberger\DbExpressions\DbExpressionsServiceProvider;
use PhilipRehberger\DbExpressions\Facades\DbExpressions;

class DatabaseExpressionsTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DbExpressionsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    // ---------------------------------------------------------------
    // driver() and isSqlite()
    // ---------------------------------------------------------------

    public function test_driver_returns_string(): void
    {
        $driver = DatabaseExpressions::driver();

        $this->assertIsString($driver);
    }

    public function test_driver_returns_sqlite_in_test_environment(): void
    {
        $this->assertSame('sqlite', DatabaseExpressions::driver());
    }

    public function test_is_sqlite_returns_true_in_test_environment(): void
    {
        $this->assertTrue(DatabaseExpressions::isSqlite());
    }

    // ---------------------------------------------------------------
    // dateTruncHour
    // ---------------------------------------------------------------

    public function test_date_trunc_hour_returns_string(): void
    {
        $result = DatabaseExpressions::dateTruncHour('created_at');

        $this->assertIsString($result);
    }

    public function test_date_trunc_hour_sqlite_contains_strftime(): void
    {
        $result = DatabaseExpressions::dateTruncHour('created_at');

        $this->assertStringContainsString('strftime', $result);
    }

    public function test_date_trunc_hour_sqlite_contains_hour_format(): void
    {
        $result = DatabaseExpressions::dateTruncHour('created_at');

        $this->assertStringContainsString('%H:00:00', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    public function test_date_trunc_hour_sqlite_contains_column_name(): void
    {
        $result = DatabaseExpressions::dateTruncHour('invoices.created_at');

        $this->assertStringContainsString('invoices.created_at', $result);
    }

    // ---------------------------------------------------------------
    // dateTruncDay
    // ---------------------------------------------------------------

    public function test_date_trunc_day_returns_string(): void
    {
        $result = DatabaseExpressions::dateTruncDay('created_at');

        $this->assertIsString($result);
    }

    public function test_date_trunc_day_sqlite_contains_strftime(): void
    {
        $result = DatabaseExpressions::dateTruncDay('created_at');

        $this->assertStringContainsString('strftime', $result);
    }

    public function test_date_trunc_day_sqlite_contains_date_format(): void
    {
        $result = DatabaseExpressions::dateTruncDay('created_at');

        $this->assertStringContainsString('%Y-%m-%d', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // dateTruncWeek
    // ---------------------------------------------------------------

    public function test_date_trunc_week_returns_string(): void
    {
        $result = DatabaseExpressions::dateTruncWeek('created_at');

        $this->assertIsString($result);
    }

    public function test_date_trunc_week_sqlite_contains_strftime(): void
    {
        $result = DatabaseExpressions::dateTruncWeek('created_at');

        $this->assertStringContainsString('strftime', $result);
    }

    public function test_date_trunc_week_sqlite_contains_week_format(): void
    {
        $result = DatabaseExpressions::dateTruncWeek('created_at');

        $this->assertStringContainsString('%Y-%W', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // dateTruncMonth
    // ---------------------------------------------------------------

    public function test_date_trunc_month_returns_string(): void
    {
        $result = DatabaseExpressions::dateTruncMonth('created_at');

        $this->assertIsString($result);
    }

    public function test_date_trunc_month_sqlite_contains_strftime(): void
    {
        $result = DatabaseExpressions::dateTruncMonth('created_at');

        $this->assertStringContainsString('strftime', $result);
    }

    public function test_date_trunc_month_sqlite_contains_month_format(): void
    {
        $result = DatabaseExpressions::dateTruncMonth('created_at');

        $this->assertStringContainsString('%Y-%m', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // dateTruncYear
    // ---------------------------------------------------------------

    public function test_date_trunc_year_returns_string(): void
    {
        $result = DatabaseExpressions::dateTruncYear('created_at');

        $this->assertIsString($result);
    }

    public function test_date_trunc_year_sqlite_contains_strftime(): void
    {
        $result = DatabaseExpressions::dateTruncYear('created_at');

        $this->assertStringContainsString('strftime', $result);
    }

    public function test_date_trunc_year_sqlite_contains_year_format(): void
    {
        $result = DatabaseExpressions::dateTruncYear('created_at');

        $this->assertStringContainsString('%Y', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // dateFormat dispatcher
    // ---------------------------------------------------------------

    public function test_date_format_hour_delegates_to_date_trunc_hour(): void
    {
        $result = DatabaseExpressions::dateFormat('created_at', 'hour');
        $expected = DatabaseExpressions::dateTruncHour('created_at');

        $this->assertSame($expected, $result);
    }

    public function test_date_format_day_delegates_to_date_trunc_day(): void
    {
        $result = DatabaseExpressions::dateFormat('created_at', 'day');
        $expected = DatabaseExpressions::dateTruncDay('created_at');

        $this->assertSame($expected, $result);
    }

    public function test_date_format_week_delegates_to_date_trunc_week(): void
    {
        $result = DatabaseExpressions::dateFormat('created_at', 'week');
        $expected = DatabaseExpressions::dateTruncWeek('created_at');

        $this->assertSame($expected, $result);
    }

    public function test_date_format_month_delegates_to_date_trunc_month(): void
    {
        $result = DatabaseExpressions::dateFormat('created_at', 'month');
        $expected = DatabaseExpressions::dateTruncMonth('created_at');

        $this->assertSame($expected, $result);
    }

    public function test_date_format_year_delegates_to_date_trunc_year(): void
    {
        $result = DatabaseExpressions::dateFormat('created_at', 'year');
        $expected = DatabaseExpressions::dateTruncYear('created_at');

        $this->assertSame($expected, $result);
    }

    public function test_date_format_unknown_period_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Unknown date format period/');

        DatabaseExpressions::dateFormat('created_at', 'quarter');
    }

    public function test_date_format_empty_period_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Unknown date format period/');

        DatabaseExpressions::dateFormat('created_at', '');
    }

    // ---------------------------------------------------------------
    // extractHour
    // ---------------------------------------------------------------

    public function test_extract_hour_returns_string(): void
    {
        $result = DatabaseExpressions::extractHour('created_at');

        $this->assertIsString($result);
    }

    public function test_extract_hour_sqlite_contains_strftime(): void
    {
        $result = DatabaseExpressions::extractHour('created_at');

        $this->assertStringContainsString('strftime', $result);
    }

    public function test_extract_hour_sqlite_contains_cast_and_integer(): void
    {
        $result = DatabaseExpressions::extractHour('created_at');

        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('INTEGER', $result);
        $this->assertStringContainsString('%H', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // extractDay
    // ---------------------------------------------------------------

    public function test_extract_day_returns_string(): void
    {
        $result = DatabaseExpressions::extractDay('created_at');

        $this->assertIsString($result);
    }

    public function test_extract_day_sqlite_contains_strftime_and_cast(): void
    {
        $result = DatabaseExpressions::extractDay('created_at');

        $this->assertStringContainsString('strftime', $result);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('%d', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // extractWeek
    // ---------------------------------------------------------------

    public function test_extract_week_returns_string(): void
    {
        $result = DatabaseExpressions::extractWeek('created_at');

        $this->assertIsString($result);
    }

    public function test_extract_week_sqlite_contains_strftime_and_cast(): void
    {
        $result = DatabaseExpressions::extractWeek('created_at');

        $this->assertStringContainsString('strftime', $result);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('%W', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // extractMonth
    // ---------------------------------------------------------------

    public function test_extract_month_returns_string(): void
    {
        $result = DatabaseExpressions::extractMonth('created_at');

        $this->assertIsString($result);
    }

    public function test_extract_month_sqlite_contains_strftime_and_cast(): void
    {
        $result = DatabaseExpressions::extractMonth('created_at');

        $this->assertStringContainsString('strftime', $result);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('%m', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // extractYear
    // ---------------------------------------------------------------

    public function test_extract_year_returns_string(): void
    {
        $result = DatabaseExpressions::extractYear('created_at');

        $this->assertIsString($result);
    }

    public function test_extract_year_sqlite_contains_strftime_and_cast(): void
    {
        $result = DatabaseExpressions::extractYear('created_at');

        $this->assertStringContainsString('strftime', $result);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('%Y', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // extractQuarter
    // ---------------------------------------------------------------

    public function test_extract_quarter_returns_string(): void
    {
        $result = DatabaseExpressions::extractQuarter('created_at');

        $this->assertIsString($result);
    }

    public function test_extract_quarter_sqlite_contains_strftime_and_arithmetic(): void
    {
        $result = DatabaseExpressions::extractQuarter('created_at');

        $this->assertStringContainsString('strftime', $result);
        $this->assertStringContainsString('%m', $result);
        $this->assertStringContainsString('created_at', $result);
        // Verify the quarter arithmetic: ((month - 1) / 3) + 1
        $this->assertStringContainsString('/ 3', $result);
        $this->assertStringContainsString('+ 1', $result);
    }

    // ---------------------------------------------------------------
    // extractMinute
    // ---------------------------------------------------------------

    public function test_extract_minute_returns_string(): void
    {
        $result = DatabaseExpressions::extractMinute('created_at');

        $this->assertIsString($result);
    }

    public function test_extract_minute_sqlite_contains_strftime_and_cast(): void
    {
        $result = DatabaseExpressions::extractMinute('created_at');

        $this->assertStringContainsString('strftime', $result);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('%M', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // extractSecond
    // ---------------------------------------------------------------

    public function test_extract_second_returns_string(): void
    {
        $result = DatabaseExpressions::extractSecond('created_at');

        $this->assertIsString($result);
    }

    public function test_extract_second_sqlite_contains_strftime_and_cast(): void
    {
        $result = DatabaseExpressions::extractSecond('created_at');

        $this->assertStringContainsString('strftime', $result);
        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('%S', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    // ---------------------------------------------------------------
    // addDays
    // ---------------------------------------------------------------

    public function test_add_days_returns_string(): void
    {
        $result = DatabaseExpressions::addDays('created_at', 7);

        $this->assertIsString($result);
    }

    public function test_add_days_sqlite_contains_datetime_and_days(): void
    {
        $result = DatabaseExpressions::addDays('created_at', 7);

        $this->assertStringContainsString('datetime', $result);
        $this->assertStringContainsString('+7 days', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    public function test_add_days_sqlite_accepts_table_qualified_column(): void
    {
        $result = DatabaseExpressions::addDays('orders.created_at', 30);

        $this->assertStringContainsString('orders.created_at', $result);
        $this->assertStringContainsString('+30 days', $result);
    }

    // ---------------------------------------------------------------
    // subtractDays
    // ---------------------------------------------------------------

    public function test_subtract_days_returns_string(): void
    {
        $result = DatabaseExpressions::subtractDays('created_at', 7);

        $this->assertIsString($result);
    }

    public function test_subtract_days_sqlite_contains_datetime_and_days(): void
    {
        $result = DatabaseExpressions::subtractDays('created_at', 7);

        $this->assertStringContainsString('datetime', $result);
        $this->assertStringContainsString('-7 days', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    public function test_subtract_days_sqlite_accepts_table_qualified_column(): void
    {
        $result = DatabaseExpressions::subtractDays('orders.created_at', 30);

        $this->assertStringContainsString('orders.created_at', $result);
        $this->assertStringContainsString('-30 days', $result);
    }

    // ---------------------------------------------------------------
    // dateDiffDays
    // ---------------------------------------------------------------

    public function test_date_diff_days_returns_string(): void
    {
        $result = DatabaseExpressions::dateDiffDays('completed_at', 'created_at');

        $this->assertIsString($result);
    }

    public function test_date_diff_days_sqlite_contains_julianday(): void
    {
        $result = DatabaseExpressions::dateDiffDays('completed_at', 'created_at');

        $this->assertStringContainsString('julianday', $result);
    }

    public function test_date_diff_days_sqlite_contains_both_columns(): void
    {
        $result = DatabaseExpressions::dateDiffDays('completed_at', 'created_at');

        $this->assertStringContainsString('completed_at', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    public function test_date_diff_days_sqlite_contains_cast_integer(): void
    {
        $result = DatabaseExpressions::dateDiffDays('completed_at', 'created_at');

        $this->assertStringContainsString('CAST', $result);
        $this->assertStringContainsString('INTEGER', $result);
    }

    // ---------------------------------------------------------------
    // dateDiffHours
    // ---------------------------------------------------------------

    public function test_date_diff_hours_returns_string(): void
    {
        $result = DatabaseExpressions::dateDiffHours('completed_at', 'created_at');

        $this->assertIsString($result);
    }

    public function test_date_diff_hours_sqlite_contains_julianday(): void
    {
        $result = DatabaseExpressions::dateDiffHours('completed_at', 'created_at');

        $this->assertStringContainsString('julianday', $result);
    }

    public function test_date_diff_hours_sqlite_contains_both_columns(): void
    {
        $result = DatabaseExpressions::dateDiffHours('completed_at', 'created_at');

        $this->assertStringContainsString('completed_at', $result);
        $this->assertStringContainsString('created_at', $result);
    }

    public function test_date_diff_hours_sqlite_multiplies_by_24(): void
    {
        $result = DatabaseExpressions::dateDiffHours('completed_at', 'created_at');

        $this->assertStringContainsString('* 24', $result);
    }

    // ---------------------------------------------------------------
    // guardColumn — injection protection
    // ---------------------------------------------------------------

    public function test_guard_column_allows_simple_column_name(): void
    {
        // Should not throw — valid names pass through
        $this->assertIsString(DatabaseExpressions::dateTruncDay('created_at'));
        $this->assertIsString(DatabaseExpressions::dateTruncDay('updated_at'));
        $this->assertIsString(DatabaseExpressions::dateTruncDay('invoices.created_at'));
        $this->assertIsString(DatabaseExpressions::dateTruncDay('table_name.column_name'));
    }

    public function test_guard_column_allows_alphanumeric_with_underscores_and_dots(): void
    {
        $this->assertIsString(DatabaseExpressions::extractMonth('abc123'));
        $this->assertIsString(DatabaseExpressions::extractMonth('a.b.c'));
        $this->assertIsString(DatabaseExpressions::extractMonth('ABC_DEF'));
    }

    public function test_guard_column_throws_for_semicolon_injection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Invalid column name/');

        DatabaseExpressions::dateTruncDay('created_at; DROP TABLE users');
    }

    public function test_guard_column_throws_for_single_quote_injection(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::dateTruncDay("created_at' OR '1'='1");
    }

    public function test_guard_column_throws_for_comment_injection(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::dateTruncDay('created_at--');
    }

    public function test_guard_column_throws_for_space_in_column_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::dateTruncDay('created at');
    }

    public function test_guard_column_throws_for_parentheses(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::extractHour('SLEEP(5)');
    }

    public function test_guard_column_throws_for_backtick(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::extractMonth('`created_at`');
    }

    public function test_guard_column_throws_on_date_diff_days_first_argument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::dateDiffDays('completed_at; --', 'created_at');
    }

    public function test_guard_column_throws_on_date_diff_days_second_argument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::dateDiffDays('completed_at', 'created_at; --');
    }

    public function test_guard_column_throws_on_date_diff_hours_first_argument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::dateDiffHours("col' OR 1=1", 'created_at');
    }

    public function test_guard_column_throws_on_date_diff_hours_second_argument(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::dateDiffHours('completed_at', 'SLEEP(5)');
    }

    public function test_guard_column_exception_message_contains_invalid_column_name(): void
    {
        $badColumn = 'col; DROP TABLE users';

        try {
            DatabaseExpressions::dateTruncDay($badColumn);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Invalid column name', $e->getMessage());
            $this->assertStringContainsString($badColumn, $e->getMessage());
        }
    }

    // ---------------------------------------------------------------
    // Table-qualified column names
    // ---------------------------------------------------------------

    public function test_all_date_trunc_methods_accept_table_qualified_column(): void
    {
        $column = 'orders.created_at';

        $this->assertStringContainsString($column, DatabaseExpressions::dateTruncHour($column));
        $this->assertStringContainsString($column, DatabaseExpressions::dateTruncDay($column));
        $this->assertStringContainsString($column, DatabaseExpressions::dateTruncWeek($column));
        $this->assertStringContainsString($column, DatabaseExpressions::dateTruncMonth($column));
        $this->assertStringContainsString($column, DatabaseExpressions::dateTruncYear($column));
    }

    public function test_all_extract_methods_accept_table_qualified_column(): void
    {
        $column = 'orders.created_at';

        $this->assertStringContainsString($column, DatabaseExpressions::extractHour($column));
        $this->assertStringContainsString($column, DatabaseExpressions::extractDay($column));
        $this->assertStringContainsString($column, DatabaseExpressions::extractWeek($column));
        $this->assertStringContainsString($column, DatabaseExpressions::extractMonth($column));
        $this->assertStringContainsString($column, DatabaseExpressions::extractYear($column));
        $this->assertStringContainsString($column, DatabaseExpressions::extractQuarter($column));
        $this->assertStringContainsString($column, DatabaseExpressions::extractMinute($column));
        $this->assertStringContainsString($column, DatabaseExpressions::extractSecond($column));
    }

    // ---------------------------------------------------------------
    // Facade delegation
    // ---------------------------------------------------------------

    public function test_facade_delegates_date_trunc_month(): void
    {
        $direct = DatabaseExpressions::dateTruncMonth('created_at');
        $facade = DbExpressions::dateTruncMonth('created_at');

        $this->assertSame($direct, $facade);
    }

    public function test_facade_delegates_extract_quarter(): void
    {
        $direct = DatabaseExpressions::extractQuarter('created_at');
        $facade = DbExpressions::extractQuarter('created_at');

        $this->assertSame($direct, $facade);
    }

    public function test_facade_delegates_date_diff_days(): void
    {
        $direct = DatabaseExpressions::dateDiffDays('completed_at', 'created_at');
        $facade = DbExpressions::dateDiffDays('completed_at', 'created_at');

        $this->assertSame($direct, $facade);
    }

    public function test_facade_delegates_extract_minute(): void
    {
        $direct = DatabaseExpressions::extractMinute('created_at');
        $facade = DbExpressions::extractMinute('created_at');

        $this->assertSame($direct, $facade);
    }

    public function test_facade_delegates_add_days(): void
    {
        $direct = DatabaseExpressions::addDays('created_at', 7);
        $facade = DbExpressions::addDays('created_at', 7);

        $this->assertSame($direct, $facade);
    }

    public function test_facade_delegates_subtract_days(): void
    {
        $direct = DatabaseExpressions::subtractDays('created_at', 14);
        $facade = DbExpressions::subtractDays('created_at', 14);

        $this->assertSame($direct, $facade);
    }

    // ---------------------------------------------------------------
    // guardColumn on new methods
    // ---------------------------------------------------------------

    public function test_guard_column_throws_on_add_days_invalid_column(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::addDays('col; DROP TABLE users', 7);
    }

    public function test_guard_column_throws_on_subtract_days_invalid_column(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::subtractDays('col; DROP TABLE users', 7);
    }

    public function test_guard_column_throws_on_extract_minute_invalid_column(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::extractMinute('SLEEP(5)');
    }

    public function test_guard_column_throws_on_extract_second_invalid_column(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DatabaseExpressions::extractSecond('SLEEP(5)');
    }

    // ---------------------------------------------------------------
    // Week format verification
    // ---------------------------------------------------------------

    public function test_date_trunc_week_sqlite_uses_w_format(): void
    {
        $result = DatabaseExpressions::dateTruncWeek('created_at');

        $this->assertStringContainsString('%Y-%W', $result);
    }

    // ---------------------------------------------------------------
    // Table-qualified columns in dateDiff
    // ---------------------------------------------------------------

    public function test_date_diff_days_accepts_table_qualified_columns(): void
    {
        $result = DatabaseExpressions::dateDiffDays('orders.completed_at', 'orders.created_at');

        $this->assertStringContainsString('orders.completed_at', $result);
        $this->assertStringContainsString('orders.created_at', $result);
    }

    // ---------------------------------------------------------------
    // All valid periods loop
    // ---------------------------------------------------------------

    public function test_date_format_accepts_all_valid_periods(): void
    {
        $validPeriods = ['hour', 'day', 'week', 'month', 'year'];

        foreach ($validPeriods as $period) {
            $result = DatabaseExpressions::dateFormat('created_at', $period);
            $this->assertIsString($result, "dateFormat should return a string for period '{$period}'");
        }
    }
}
