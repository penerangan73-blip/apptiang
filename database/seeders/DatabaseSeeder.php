<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\Pole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RbacUsersSeeder::class);

        $superadmin = User::query()->where('email', 'superadmin@pju.local')->firstOrFail();
        $surveyor = User::query()->where('email', 'surveyor@pju.local')->firstOrFail();

        DailyReport::query()->delete();
        Pole::query()->delete();

        $poles = collect($this->gunungkidulPoleData())->map(fn (array $data) => Pole::query()->create([
            ...$data,
            'created_by_id' => $superadmin->id,
            'updated_by_id' => $surveyor->id,
        ]));

        foreach ($poles->take(12) as $index => $pole) {
            DailyReport::query()->create([
                'user_id' => $surveyor->id,
                'pole_id' => $pole->id,
                'report_date' => now()->toDateString(),
                'notes' => $this->reportNotes($index, $pole->customer_id),
            ]);
        }
    }

    private function gunungkidulPoleData(): array
    {
        $locations = [
            ['kapanewon' => 'Wonosari', 'street' => 'Jalan Brigjen Katamso Wonosari', 'lat' => -7.9653, 'lon' => 110.6031],
            ['kapanewon' => 'Playen', 'street' => 'Jalan Playen-Paliyan', 'lat' => -7.9438, 'lon' => 110.5271],
            ['kapanewon' => 'Patuk', 'street' => 'Jalan Patuk-Wonosari', 'lat' => -7.8578, 'lon' => 110.4796],
            ['kapanewon' => 'Paliyan', 'street' => 'Jalan Paliyan-Saptosari', 'lat' => -8.0012, 'lon' => 110.5198],
            ['kapanewon' => 'Panggang', 'street' => 'Jalan Panggang-Saptosari', 'lat' => -8.0235, 'lon' => 110.4212],
            ['kapanewon' => 'Saptosari', 'street' => 'Jalan Saptosari-Paliyan', 'lat' => -8.0483, 'lon' => 110.5144],
            ['kapanewon' => 'Tepus', 'street' => 'Jalan Tepus-Sumberwungu', 'lat' => -8.0828, 'lon' => 110.6356],
            ['kapanewon' => 'Tanjungsari', 'street' => 'Jalan Tanjungsari-Kemadang', 'lat' => -8.0649, 'lon' => 110.5758],
            ['kapanewon' => 'Rongkop', 'street' => 'Jalan Rongkop-Semugih', 'lat' => -8.0401, 'lon' => 110.7685],
            ['kapanewon' => 'Semanu', 'street' => 'Jalan Semanu-Pracimantoro', 'lat' => -8.0006, 'lon' => 110.6466],
            ['kapanewon' => 'Ponjong', 'street' => 'Jalan Ponjong-Semanu', 'lat' => -7.9892, 'lon' => 110.7174],
            ['kapanewon' => 'Karangmojo', 'street' => 'Jalan Karangmojo-Semin', 'lat' => -7.9471, 'lon' => 110.6617],
            ['kapanewon' => 'Semin', 'street' => 'Jalan Semin-Ngawen', 'lat' => -7.8598, 'lon' => 110.7515],
            ['kapanewon' => 'Ngawen', 'street' => 'Jalan Ngawen-Semin', 'lat' => -7.8295, 'lon' => 110.7048],
            ['kapanewon' => 'Nglipar', 'street' => 'Jalan Nglipar-Ngawen', 'lat' => -7.8962, 'lon' => 110.6139],
            ['kapanewon' => 'Gedangsari', 'street' => 'Jalan Gedangsari-Nglipar', 'lat' => -7.8159, 'lon' => 110.5884],
            ['kapanewon' => 'Girisubo', 'street' => 'Jalan Girisubo-Jepitu', 'lat' => -8.0764, 'lon' => 110.7988],
            ['kapanewon' => 'Purwosari', 'street' => 'Jalan Purwosari-Panggang', 'lat' => -8.0168, 'lon' => 110.3582],
            ['kapanewon' => 'Wonosari', 'street' => 'Jalan Siyono-Wonosari', 'lat' => -7.9492, 'lon' => 110.5891],
            ['kapanewon' => 'Karangmojo', 'street' => 'Jalan Bejiharjo-Karangmojo', 'lat' => -7.9286, 'lon' => 110.6492],
        ];
        $conditions = ['kokoh', 'kokoh', 'kokoh', 'keropos', 'miring', 'berkarat', 'lainnya'];
        $poleTypes = ['tiang_baja_galvanis', 'tiang_beton', 'tiang_cor', 'tiang_kayu', 'tiang_bambu', 'menempel_tiang_pln', 'menempel_tembok', 'menempel_pohon', 'tanpa_penyangga'];
        $ornamentTypes = ['single_arm', 'double_arm', 'high_mast', 'lainnya'];
        $lampTypes = ['led', 'cfl', 'hps', 'son_t', 'mercury', 'lainnya'];
        $lampStatuses = ['menyala', 'menyala', 'menyala', 'mati', 'redup', 'berkedip'];
        $lampHouseTypes = ['caping', 'konvensional', 'tidak_ada'];
        $lampHouseConditions = ['baik', 'baik', 'rusak'];
        $ownerships = ['dpu_kp', 'dishub', 'pln', 'lainnya'];
        $powerUsages = [60, 80, 90, 100, 120, 150];

        mt_srand(20260507);

        return collect(range(1, 50))->map(function (int $number) use (
            $locations,
            $conditions,
            $poleTypes,
            $ornamentTypes,
            $lampTypes,
            $lampStatuses,
            $lampHouseTypes,
            $lampHouseConditions,
            $ownerships,
            $powerUsages,
        ) {
            $poleType = $this->pick($poleTypes);
            $location = $locations[($number - 1) % count($locations)];
            $sequence = intdiv($number - 1, count($locations)) + 1;

            return [
                'customer_id' => sprintf('GK-PEL-%03d', $number),
                'street_name' => "{$location['street']} - Titik {$sequence}",
                'address' => "{$location['street']} - Titik {$sequence}",
                'district' => $location['kapanewon'],
                'regency' => 'Gunungkidul',
                'province' => 'Daerah Istimewa Yogyakarta',
                'latitude' => $this->jitter($location['lat'], 0.008),
                'longitude' => $this->jitter($location['lon'], 0.008),
                'power_usage' => $this->pick($powerUsages),
                'condition' => $this->pick($conditions),
                'pole_type' => $poleType,
                'height' => in_array($poleType, ['menempel_tiang_pln', 'menempel_tembok', 'menempel_pohon', 'tanpa_penyangga'], true) ? null : $this->randomFloat(5.5, 10.5, 1),
                'ornament_type' => $this->pick($ornamentTypes),
                'lamp_type' => $this->pick($lampTypes),
                'lamp_status' => $this->pick($lampStatuses),
                'lamp_house_type' => $this->pick($lampHouseTypes),
                'lamp_house_condition' => $this->pick($lampHouseConditions),
                'light_point_count' => mt_rand(1, 4),
                'kwh_meter' => $number % 3 === 0 ? 'belum' : 'sudah_ada',
                'ownership' => $this->pick($ownerships),
                'photo_url' => null,
                'notes' => "Data sample area Kabupaten Gunungkidul, Kapanewon {$location['kapanewon']}.",
            ];
        })->all();
    }

    private function randomFloat(float $min, float $max, int $precision = 6): float
    {
        return round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), $precision);
    }

    private function pick(array $values): mixed
    {
        return $values[mt_rand(0, count($values) - 1)];
    }

    private function jitter(float $center, float $radius): float
    {
        return round($center + $this->randomFloat(-$radius, $radius), 6);
    }

    private function reportNotes(int $index, string $customerId): string
    {
        $notes = [
            'Validasi koordinat dan lokasi selesai.',
            'Lampu redup, perlu pemeriksaan armatur.',
            'Panel bawah perlu dikunci ulang.',
            'Tiang dalam kondisi kokoh saat survey.',
            'Kondisi rumah lampu perlu dicek ulang.',
            'Foto aset perlu diperbarui pada kunjungan berikutnya.',
        ];

        return "{$customerId}: ".$notes[$index % count($notes)];
    }
}
