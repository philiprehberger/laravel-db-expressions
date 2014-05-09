<?php

declare(strict_types=1);

namespace PhilipRehberger\DbExpressions\Facades;

use Illuminate\Support\Facades\Facade;
use PhilipRehberger\DbExpressions\DatabaseExpressions;

/**
 * @method static string driver()
 * @method static bool isSqlite()
 * @method static string dateTruncHour(string $column)
 * @method static string dateTruncDay(string $column)
 * @method static string dateTruncWeek(string $column)
 * @method static string dateTruncMonth(string $column)
 * @method static string dateTruncYear(string $column)
 * @method static string dateFormat(string $column, string $groupBy)
 * @method static string extractHour(string $column)
 * @method static string extractDay(string $column)
 * @method static string extractWeek(string $column)
 * @method static string extractMonth(string $column)
 * @method static string extractYear(string $column)
 * @method static string extractQuarter(string $column)
 * @method static string dateDiffDays(string $column1, string $column2)
 * @method static string dateDiffHours(string $column1, string $column2)
 *
 * @see \PhilipRehberger\DbExpressions\DatabaseExpressions
 */
class DbExpressions extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return DatabaseExpressions::class;
    }
}
