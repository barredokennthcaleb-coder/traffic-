<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * One place for the report period rules: which types exist and how each one
 * turns the query string into a concrete date window.
 *
 * The Revenue Report, the Violations Summary Report and the Violator Reports
 * all read periods the same way, so a "week" means the same Monday-to-Sunday
 * on every screen and a "month" always covers the whole calendar month.
 *
 * Every value is normalised here — years, months and dates are validated and
 * re-formatted into plain Y-m-d strings — so a malformed query string can
 * never reach a model query as anything but a well-formed date.
 */
class ReportPeriod
{
    /** The only period types the reports understand. */
    public const TYPES = ['weekly', 'monthly', 'yearly', 'custom'];

    public static function isValid(string $periodType): bool
    {
        return in_array($periodType, self::TYPES, true);
    }

    /**
     * Resolves startDate, endDate, year, and month from the query-string
     * parameters of the given request, based on the period type.
     *
     * @return array{startDate: string, endDate: string, year: string, month: string}
     */
    public static function resolve(IncomingRequest $request, string $periodType): array
    {
        $year  = (int) date('Y');
        $month = (int) date('n');

        if ($periodType === 'yearly') {
            $year      = self::cleanYear($request->getGet('year'));
            $startDate = sprintf('%04d-01-01', $year);
            $endDate   = sprintf('%04d-12-31', $year);

        } elseif ($periodType === 'monthly') {
            $monthYear = $request->getGet('month_year');

            if (is_string($monthYear) && preg_match('/^(\d{4})-(\d{2})$/', $monthYear, $mMatches)) {
                $year  = (int) $mMatches[1];
                $month = (int) $mMatches[2];
            } else {
                $year  = self::cleanYear($request->getGet('year'));
                $month = self::cleanMonth($request->getGet('month'));
            }

            $startDate = date('Y-m-d', strtotime(sprintf('%04d-%02d-01', $year, $month)));
            $endDate   = date('Y-m-t', strtotime($startDate));

        } elseif ($periodType === 'weekly') {
            $weekRef = $request->getGet('start_date');
            $ts      = is_string($weekRef) && $weekRef !== '' ? strtotime($weekRef) : false;
            $ts      = $ts ?: strtotime(date('Y-m-d'));

            $dow       = (int) date('N', $ts); // 1 = Mon … 7 = Sun (ISO-8601)
            $startDate = date('Y-m-d', strtotime('-' . ($dow - 1) . ' days', $ts));
            $endDate   = date('Y-m-d', strtotime('+' . (7 - $dow) . ' days', $ts));

        } else {
            // custom range — fall back to current month if nothing valid was supplied
            $startDate = self::cleanDate($request->getGet('start_date'));
            $endDate   = self::cleanDate($request->getGet('end_date'));

            if ($startDate === null || $endDate === null) {
                $startDate = date('Y-m-01');
                $endDate   = date('Y-m-t');
            }
        }

        return [
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'year'      => (string) $year,
            'month'     => sprintf('%02d', $month),
        ];
    }

    /**
     * A usable four-digit year, or the current one when the input is junk.
     *
     * @param mixed $value
     */
    private static function cleanYear($value): int
    {
        $year = is_numeric($value) ? (int) $value : 0;

        return ($year >= 1970 && $year <= 2100) ? $year : (int) date('Y');
    }

    /**
     * A calendar month between 1 and 12, or the current one when the input is junk.
     *
     * @param mixed $value
     */
    private static function cleanMonth($value): int
    {
        $month = is_numeric($value) ? (int) $value : 0;

        return ($month >= 1 && $month <= 12) ? $month : (int) date('n');
    }

    /**
     * A plain Y-m-d date, or null when the input is missing or unreadable.
     *
     * @param mixed $value
     */
    private static function cleanDate($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $ts = strtotime($value);

        return $ts === false ? null : date('Y-m-d', $ts);
    }
}
