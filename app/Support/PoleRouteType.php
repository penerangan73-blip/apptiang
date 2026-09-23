<?php

namespace App\Support;

class PoleRouteType
{
    /**
     * @param  array<string, bool>  $allPoleNumbers
     * @return array{tipe: string, sumber: string}
     */
    public static function determine(string $number, array $allPoleNumbers): array
    {
        $number = trim($number);
        $lastSlashPosition = strrpos($number, '/');

        if ($lastSlashPosition === false) {
            return self::source();
        }

        $parent = trim(substr($number, 0, $lastSlashPosition));
        $suffix = trim(substr($number, $lastSlashPosition + 1));

        if ($parent === '' || $suffix === '') {
            return self::source();
        }

        if (preg_match('/^0\d{2}$/', $suffix) === 1) {
            return self::sub($parent);
        }

        if (preg_match('/^\d{3}$/', $suffix) === 1 && isset($allPoleNumbers[$parent])) {
            return self::sub($parent);
        }

        return self::source();
    }

    /**
     * @return array{tipe: string, sumber: string}
     */
    private static function source(): array
    {
        return ['tipe' => 'Sumber', 'sumber' => '-'];
    }

    /**
     * @return array{tipe: string, sumber: string}
     */
    private static function sub(string $parent): array
    {
        return ['tipe' => 'Sub', 'sumber' => $parent];
    }
}
