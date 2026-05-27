<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pole;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class MapPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('query', ''));
        $condition = $request->query('condition');

        $poles = $this->filteredPoleQuery($query, $condition)
            ->with(['createdBy:id,name,role,email', 'updatedBy:id,name,role,email'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $dialogPoles = $poles->getCollection()->unique('id')->values();
        $user = $request->user();

        return view('pages.map.index', [
            'poles' => $poles,
            'dialogPoles' => $dialogPoles,
            'query' => $query,
            'condition' => $condition,
            'user' => $user,
            'canManagePoles' => in_array($user->role, ['superadmin', 'surveyor'], true),
        ]);
    }

    public function markers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'condition' => ['sometimes', 'nullable', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh', 'lainnya'])],
            'summary' => ['sometimes', 'boolean'],
        ]);
        $summary = $request->boolean('summary');

        $markers = $this->filteredPoleQuery(
            trim((string) ($validated['query'] ?? '')),
            $validated['condition'] ?? null
        )
            ->select($this->poleColumns($summary ? [
                'id',
                'sub_name',
                'customer_id',
                'street_name',
                'address',
                'village',
                'district',
                'regency',
                'province',
                'latitude',
                'longitude',
                'power_usage',
                'condition',
            ] : [
                'id',
                'sub_name',
                'customer_id',
                'street_name',
                'address',
                'village',
                'district',
                'regency',
                'province',
                'latitude',
                'longitude',
                'power_usage',
                'condition',
                'pole_type',
                'height',
                'ornament_type',
                'lamp_type',
                'lamp_status',
                'lamp_house_type',
                'lamp_house_condition',
                'light_point_count',
                'kwh_meter',
                'ownership',
                'photo_url',
                'notes',
            ]))
            ->latest()
            ->get();

        return response()->json([
            'data' => $markers->map(fn (Pole $pole) => [
                ...$pole->toArray(),
                'display_name' => $pole->displayName(),
                'address_line' => $pole->addressLine(),
                'administrative_address' => $pole->administrativeAddress(),
                'location_label' => $pole->locationLabel(),
            ])->values(),
        ]);
    }

    public function export(Request $request, string $format): Response
    {
        $validated = $request->validate([
            'query' => ['sometimes', 'nullable', 'string', 'max:120'],
            'condition' => ['sometimes', 'nullable', Rule::in(['keropos', 'miring', 'berkarat', 'kokoh', 'lainnya'])],
            'include_layers' => ['sometimes', 'boolean'],
        ]);
        $includeLayers = $request->boolean('include_layers');

        $poles = $this->exportPoleQuery(
            trim((string) ($validated['query'] ?? '')),
            $validated['condition'] ?? null
        )->get();

        return match ($format) {
            'geojson' => $this->downloadGeoJson($poles, $includeLayers),
            'kml' => $this->downloadKml($poles, $includeLayers),
            'shp' => $this->downloadShapefile($poles, $includeLayers),
        };
    }

    private function filteredPoleQuery(string $query, ?string $condition)
    {
        return Pole::query()->search($query)->condition($condition);
    }

    private function exportPoleQuery(string $query, ?string $condition)
    {
        return $this->filteredPoleQuery($query, $condition)
            ->select($this->poleColumns([
                'id',
                'sub_name',
                'customer_id',
                'street_name',
                'address',
                'village',
                'district',
                'regency',
                'province',
                'latitude',
                'longitude',
                'power_usage',
                'condition',
                'pole_type',
                'height',
                'ornament_type',
                'lamp_type',
                'lamp_status',
                'lamp_house_type',
                'lamp_house_condition',
                'light_point_count',
                'kwh_meter',
                'ownership',
                'notes',
                'created_at',
                'updated_at',
            ]))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->latest();
    }

    private function downloadGeoJson(Collection $poles, bool $includeLayers = false): Response
    {
        $payload = [
            'type' => 'FeatureCollection',
            'features' => $poles->map(fn (Pole $pole) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $pole->longitude, (float) $pole->latitude],
                ],
                'properties' => $this->poleExportProperties($pole),
            ])->values(),
        ];
        $content = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($includeLayers) {
            $baseName = 'data-tiang-pju-'.now()->format('Y-m-d-His');

            return $this->downloadZipWithLayerSources($baseName, $baseName.'.geojson', $content);
        }

        return response($content, 200, [
            'Content-Type' => 'application/geo+json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$this->exportFilename('geojson').'"',
        ]);
    }

    private function downloadKml(Collection $poles, bool $includeLayers = false): Response
    {
        $placemarks = $poles->map(function (Pole $pole) {
            $properties = collect($this->poleExportProperties($pole))
                ->map(fn ($value, $key) => '<Data name="'.e((string) $key).'"><value>'.e((string) $value).'</value></Data>')
                ->implode('');

            return '<Placemark>'.
                '<name>'.e($pole->displayName()).'</name>'.
                '<description>'.e($pole->locationLabel()).'</description>'.
                '<ExtendedData>'.$properties.'</ExtendedData>'.
                '<Point><coordinates>'.e((string) $pole->longitude).','.e((string) $pole->latitude).',0</coordinates></Point>'.
                '</Placemark>';
        })->implode('');

        $kml = '<?xml version="1.0" encoding="UTF-8"?>'.
            '<kml xmlns="http://www.opengis.net/kml/2.2">'.
            '<Document><name>Data Tiang PJU</name>'.$placemarks.'</Document>'.
            '</kml>';

        if ($includeLayers) {
            $baseName = 'data-tiang-pju-'.now()->format('Y-m-d-His');

            return $this->downloadZipWithLayerSources($baseName, $baseName.'.kml', $kml);
        }

        return response($kml, 200, [
            'Content-Type' => 'application/vnd.google-earth.kml+xml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$this->exportFilename('kml').'"',
        ]);
    }

    private function downloadShapefile(Collection $poles, bool $includeLayers = false): Response
    {
        $baseName = 'data-tiang-pju-'.now()->format('Y-m-d-His');
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.$baseName.'-'.uniqid();

        mkdir($directory, 0775, true);

        $records = $poles->map(fn (Pole $pole) => [
            'geometry' => [(float) $pole->longitude, (float) $pole->latitude],
            'properties' => $this->poleShapefileProperties($pole),
        ])->values();

        file_put_contents($directory.DIRECTORY_SEPARATOR.$baseName.'.shp', $this->buildPointShp($records));
        file_put_contents($directory.DIRECTORY_SEPARATOR.$baseName.'.shx', $this->buildPointShx($records));
        file_put_contents($directory.DIRECTORY_SEPARATOR.$baseName.'.dbf', $this->buildDbf($records));
        file_put_contents($directory.DIRECTORY_SEPARATOR.$baseName.'.prj', 'GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563]],PRIMEM["Greenwich",0],UNIT["degree",0.0174532925199433]]');

        $zipPath = $directory.'.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (['shp', 'shx', 'dbf', 'prj'] as $extension) {
            $zip->addFile($directory.DIRECTORY_SEPARATOR.$baseName.'.'.$extension, $baseName.'.'.$extension);
        }

        if ($includeLayers) {
            $this->addLayerSourcesToZip($zip);
        }

        $zip->close();

        foreach (glob($directory.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($directory);

        return response()->download($zipPath, $baseName.'.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function downloadZipWithLayerSources(string $baseName, string $mainFilename, string $mainContent): Response
    {
        $zipPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$baseName.'-'.uniqid().'.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString($mainFilename, $mainContent);
        $this->addLayerSourcesToZip($zip);
        $zip->close();

        return response()->download($zipPath, $baseName.'.zip', [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function addLayerSourcesToZip(ZipArchive $zip): void
    {
        foreach ($this->layerSourceFiles() as $name => $path) {
            if (is_file($path)) {
                $zip->addFile($path, 'layers/'.$name);
            }
        }
    }

    private function layerSourceFiles(): array
    {
        return [
            'administrasi-area.zip' => public_path('data/peta/administrasi_ar_340320240807001259.zip'),
            'batas-administrasi.zip' => public_path('data/peta/administrasi_ln_340320240806233638.zip'),
        ];
    }

    private function poleExportProperties(Pole $pole): array
    {
        return [
            'id' => $pole->id,
            'display_name' => $pole->displayName(),
            'customer_id' => $pole->customer_id,
            'street_name' => $pole->addressLine(),
            'address' => $pole->addressLine(),
            'village' => $pole->village,
            'district' => $pole->district,
            'regency' => $pole->regency,
            'province' => $pole->province,
            'power_usage' => $pole->power_usage,
            'condition' => $pole->condition,
            'pole_type' => $pole->pole_type,
            'height' => $pole->height,
            'ornament_type' => $pole->ornament_type,
            'lamp_type' => $pole->lamp_type,
            'lamp_status' => $pole->lamp_status,
            'lamp_house_type' => $pole->lamp_house_type,
            'lamp_house_condition' => $pole->lamp_house_condition,
            'light_point_count' => $pole->light_point_count,
            'kwh_meter' => $pole->kwh_meter,
            'ownership' => $pole->ownership,
            'notes' => $pole->notes,
            'created_at' => optional($pole->created_at)->toDateTimeString(),
            'updated_at' => optional($pole->updated_at)->toDateTimeString(),
        ];
    }

    private function poleShapefileProperties(Pole $pole): array
    {
        return [
            'ID' => (int) $pole->id,
            'DISPLAY' => $pole->displayName(),
            'CUSTOMER' => $pole->customer_id,
            'ADDRESS' => $pole->addressLine(),
            'VILLAGE' => $pole->village,
            'DISTRICT' => $pole->district,
            'REGENCY' => $pole->regency,
            'PROVINCE' => $pole->province,
            'POWER' => (int) $pole->power_usage,
            'CONDITION' => $pole->condition,
            'POLETYPE' => $pole->pole_type,
            'HEIGHT' => $pole->height,
            'ORNAMENT' => $pole->ornament_type,
            'LAMP' => $pole->lamp_type,
            'LAMPSTAT' => $pole->lamp_status,
            'HOUSE' => $pole->lamp_house_type,
            'KWH' => $pole->kwh_meter,
            'OWNER' => $pole->ownership,
        ];
    }

    private function buildPointShp(Collection $records): string
    {
        $content = '';

        foreach ($records as $index => $record) {
            [$longitude, $latitude] = $record['geometry'];
            $recordContent = pack('V', 1).pack('e', $longitude).pack('e', $latitude);
            $content .= pack('N2', $index + 1, strlen($recordContent) / 2).$recordContent;
        }

        return $this->shapeHeader($records, strlen($content)).$content;
    }

    private function buildPointShx(Collection $records): string
    {
        $content = '';
        $offset = 50;

        foreach ($records as $record) {
            $content .= pack('N2', $offset, 10);
            $offset += 14;
        }

        return $this->shapeHeader($records, strlen($content)).$content;
    }

    private function shapeHeader(Collection $records, int $contentBytes): string
    {
        [$minX, $minY, $maxX, $maxY] = $this->shapeBounds($records);

        return pack('N', 9994).
            str_repeat("\0", 20).
            pack('N', (100 + $contentBytes) / 2).
            pack('V2', 1000, 1).
            pack('e4', $minX, $minY, $maxX, $maxY).
            pack('e4', 0, 0, 0, 0);
    }

    private function shapeBounds(Collection $records): array
    {
        if ($records->isEmpty()) {
            return [0, 0, 0, 0];
        }

        $longitudes = $records->pluck('geometry.0');
        $latitudes = $records->pluck('geometry.1');

        return [
            (float) $longitudes->min(),
            (float) $latitudes->min(),
            (float) $longitudes->max(),
            (float) $latitudes->max(),
        ];
    }

    private function buildDbf(Collection $records): string
    {
        $fields = [
            ['ID', 'N', 10, 0],
            ['DISPLAY', 'C', 120, 0],
            ['CUSTOMER', 'C', 80, 0],
            ['ADDRESS', 'C', 200, 0],
            ['VILLAGE', 'C', 80, 0],
            ['DISTRICT', 'C', 80, 0],
            ['REGENCY', 'C', 80, 0],
            ['PROVINCE', 'C', 80, 0],
            ['POWER', 'N', 10, 0],
            ['CONDITION', 'C', 24, 0],
            ['POLETYPE', 'C', 40, 0],
            ['HEIGHT', 'N', 12, 2],
            ['ORNAMENT', 'C', 40, 0],
            ['LAMP', 'C', 40, 0],
            ['LAMPSTAT', 'C', 40, 0],
            ['HOUSE', 'C', 32, 0],
            ['KWH', 'C', 20, 0],
            ['OWNER', 'C', 32, 0],
        ];

        $headerLength = 32 + (count($fields) * 32) + 1;
        $recordLength = 1 + array_sum(array_column($fields, 2));
        $now = now();

        $dbf = pack('C4Vvvx20', 0x03, $now->year - 1900, $now->month, $now->day, $records->count(), $headerLength, $recordLength);

        foreach ($fields as [$name, $type, $length, $decimal]) {
            $dbf .= str_pad(substr($name, 0, 10), 11, "\0").
                $type.
                str_repeat("\0", 4).
                pack('C', $length).
                pack('C', $decimal).
                str_repeat("\0", 14);
        }

        $dbf .= "\r";

        foreach ($records as $record) {
            $dbf .= ' ';

            foreach ($fields as [$name, $type, $length, $decimal]) {
                $value = $record['properties'][$name] ?? null;
                $dbf .= $type === 'N'
                    ? $this->dbfNumber($value, $length, $decimal)
                    : $this->dbfText($value, $length);
            }
        }

        return $dbf."\x1A";
    }

    private function dbfText($value, int $length): string
    {
        return str_pad(substr((string) $value, 0, $length), $length);
    }

    private function dbfNumber($value, int $length, int $decimal): string
    {
        if ($value === null || $value === '') {
            return str_repeat(' ', $length);
        }

        $number = $decimal > 0
            ? number_format((float) $value, $decimal, '.', '')
            : (string) (int) $value;

        return str_pad(substr($number, 0, $length), $length, ' ', STR_PAD_LEFT);
    }

    private function exportFilename(string $extension): string
    {
        return 'data-tiang-pju-'.now()->format('Y-m-d-His').'.'.$extension;
    }

    private function poleColumns(array $columns): array
    {
        $existingColumns = Schema::getColumnListing('poles');

        return array_values(array_intersect($columns, $existingColumns));
    }
}
