<?php

namespace App\Helpers;

use Carbon\Carbon;
use Exception;

class DateHelper
{
    /**
     * Peta nama bulan bahasa Indonesia ke bahasa Inggris / angka
     */
    protected static array $indoMonths = [
        'januari'   => 'January',
        'jan'       => 'Jan',
        'februari'  => 'February',
        'feb'       => 'Feb',
        'maret'     => 'March',
        'mar'       => 'Mar',
        'april'     => 'April',
        'apr'       => 'Apr',
        'mei'       => 'May',
        'juni'      => 'June',
        'jun'       => 'Jun',
        'juli'      => 'July',
        'jul'       => 'Jul',
        'agustus'   => 'August',
        'agu'       => 'Aug',
        'agust'     => 'Aug',
        'september' => 'September',
        'sep'       => 'Sep',
        'oktober'   => 'October',
        'okt'       => 'Oct',
        'november'  => 'November',
        'nov'       => 'Nov',
        'desember'  => 'December',
        'des'       => 'Dec',
    ];

    /**
     * Normalisasi string tanggal dari berbagai format menjadi format standar 'YYYY-MM-DD'.
     * Mengembalikan null jika tanggal tidak valid atau tidak dapat diproses.
     *
     * @param string|null $rawDate
     * @return string|null
     */
    public static function normalize(?string $rawDate): ?string
    {
        if (!$rawDate) {
            return null;
        }

        $trimmed = trim($rawDate);
        if (empty($trimmed)) {
            return null;
        }

        // 1. Ganti nama bulan Indonesia ke Inggris jika ada (misal: "14 Januari 2012" -> "14 January 2012")
        $lower = strtolower($trimmed);
        foreach (self::$indoMonths as $indo => $eng) {
            if (str_contains($lower, $indo)) {
                $trimmed = preg_replace('/\b' . preg_quote($indo, '/') . '\b/i', $eng, $trimmed);
                break;
            }
        }

        // 2. Cobalah format eksplisit standar
        $formats = [
            'Y-m-d',
            'Y/m/d',
            'Y.m.d',
            'd/m/Y',
            'd-m-Y',
            'd.m.Y',
            'j F Y',
            'd F Y',
            'j M Y',
            'd M Y',
            'j-M-Y',
            'd-M-Y',
            'm/d/Y',
            'Y-m-d H:i:s',
        ];

        foreach ($formats as $fmt) {
            try {
                $parsed = Carbon::createFromFormat('!' . $fmt, $trimmed);
                if ($parsed) {
                    // Pastikan formatting ulang persis sama dengan input (mencegah overflow seperti 31 Feb -> 3 Mar)
                    if (strcasecmp($parsed->format($fmt), $trimmed) === 0) {
                        return $parsed->format('Y-m-d');
                    }
                }
            } catch (Exception $e) {
                // Lanjut ke format berikutnya
            }
        }

        // 3. Fallback menggunakan Carbon::parse() untuk variasi string lainnya
        try {
            $parsed = Carbon::parse($trimmed);
            $year = (int)$parsed->format('Y');
            $month = (int)$parsed->format('m');
            $day = (int)$parsed->format('d');

            if ($year >= 1900 && $year <= 2100 && checkdate($month, $day, $year)) {
                return $parsed->format('Y-m-d');
            }
        } catch (Exception $e) {
            // Unparseable
        }

        return null;
    }
}
