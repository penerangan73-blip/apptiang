<?php

namespace Tests\Unit;

use App\Support\PoleRouteType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PoleRouteTypeTest extends TestCase
{
    /**
     * @param  array<string, bool>  $allPoleNumbers
     */
    #[DataProvider('routeTypeCases')]
    public function test_it_determines_pole_route_type(string $number, array $allPoleNumbers, string $expectedType, string $expectedSource): void
    {
        $result = PoleRouteType::determine($number, $allPoleNumbers);

        $this->assertSame($expectedType, $result['tipe']);
        $this->assertSame($expectedSource, $result['sumber']);
    }

    public static function routeTypeCases(): array
    {
        return [
            'plain source' => ['TILENG_025', [], 'Sumber', '-'],
            'sub with 003 suffix' => ['Tileng_A004/003', [], 'Sub', 'Tileng_A004'],
            'sub with nested source and 001 suffix' => ['34/S3-1106/001', [], 'Sub', '34/S3-1106'],
            'sub with nested numeric parent and 016 suffix' => ['S3-601/5/016', [], 'Sub', 'S3-601/5'],
            'sub with numeric parent and 001 suffix' => ['49/001', [], 'Sub', '49'],
            'sub without parent because suffix starts with zero' => ['Bejiharjo3/001', [], 'Sub', 'Bejiharjo3'],
            'pln source with nonnumeric suffix' => ['25/S3-25', [], 'Sumber', '-'],
            'source with two digit suffix' => ['S3-20/33', [], 'Sumber', '-'],
            'source with spaces and two digit suffix' => ['S3-9 / 44', [], 'Sumber', '-'],
            'source with alphanumeric suffix' => ['S3-601/3B', [], 'Sumber', '-'],
            'source with three digit suffix and missing parent' => ['S3-110/189', [], 'Sumber', '-'],
            'source with parenthesized suffix' => ['12/350(190)', [], 'Sumber', '-'],
            'sub with three digit suffix and existing parent' => ['S3-110/189', ['S3-110' => true], 'Sub', 'S3-110'],
            'trims number and parent' => [' S3-9 / 044 ', [], 'Sub', 'S3-9'],
        ];
    }
}
