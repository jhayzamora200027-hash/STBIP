<?php

namespace Tests\Unit;

use App\Models\Region;
use App\Models\RegionItem;
use App\Services\RegionSheetImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class RegionSheetImportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('region_items');
        Schema::dropIfExists('regions');

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('createdby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });

        Schema::create('region_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('title');
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->boolean('with_expr')->default(false);
            $table->boolean('with_moa')->default(false);
            $table->integer('year_of_moa')->nullable();
            $table->boolean('with_res')->default(false);
            $table->integer('year_of_resolution')->nullable();
            $table->boolean('included_aip')->default(false);
            $table->boolean('with_adopted')->default(false);
            $table->boolean('with_replicated')->default(false);
            $table->string('status')->nullable();
            $table->string('createdby')->nullable();
            $table->string('updatedby')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('region_items');
        Schema::dropIfExists('regions');

        parent::tearDown();
    }

    public function testResolveIndexesKeepsReplicatedColumnSeparateFromAdoptedGroupHeader(): void
    {
        $service = new RegionSheetImportService();
        $method = new \ReflectionMethod($service, 'resolveIndexes');
        $method->setAccessible(true);

        $headers = [
            '',
            'title of st',
            'province',
            'name of municipality',
            'contact deatils of lgu focal (email and/or phone number)',
            'with expression of interest yes',
            'no',
            'with moa yes',
            'no',
            'year of moa',
            'with resolution yes',
            'no',
            'year of resolution',
            'included in the aip yes',
            'no',
            'adopted/replicated adopted',
            'replicated',
            'status ongoing',
            'dissolved/ inactive',
        ];

        $indexes = $method->invoke($service, $headers);

        $this->assertSame(15, $indexes['with_adopted']);
        $this->assertSame(16, $indexes['with_replicated']);
    }

    public function testImportAddsNewStsWithoutDeletingExistingOnes(): void
    {
        $service = new RegionSheetImportService();
        $region = Region::query()->create([
            'name' => 'FO CAR',
            'createdby' => 'Seeder',
            'updatedby' => 'Seeder',
        ]);

        $existingItem = RegionItem::query()->create([
            'region_id' => $region->id,
            'title' => 'Existing ST',
            'province' => 'Abra',
            'municipality' => 'Bangued',
            'with_expr' => false,
            'with_moa' => false,
            'year_of_moa' => null,
            'with_res' => false,
            'year_of_resolution' => null,
            'included_aip' => false,
            'with_adopted' => false,
            'with_replicated' => false,
            'status' => 'ongoing',
            'createdby' => 'Seeder',
            'updatedby' => 'Seeder',
        ]);

        $tempPath = $this->createGoogleSheetWorkbook([
            ['CAR', [
                ['Existing ST', 'Abra', 'Bangued', 'Yes', 'Yes', 2024, 'Yes', 2025, 'Yes', 'Adopted', 'Dissolved'],
                ['Additional ST', 'Abra', 'La Paz', 'Yes', 'No', null, 'No', null, 'No', 'Replicated', 'Ongoing'],
            ]],
        ]);

        try {
            $result = $service->importFromPath($tempPath, 'test-import.xlsx', 'Importer');
        } finally {
            @unlink($tempPath);
        }

        $this->assertSame(2, $result['items_count']);
        $this->assertSame(1, $result['regions_count']);
        $this->assertSame(1, $result['added_count']);
        $this->assertSame(1, $result['updated_count']);
        $this->assertSame(2, RegionItem::query()->count());

        $existingItem->refresh();
        $this->assertTrue($existingItem->with_expr);
        $this->assertTrue($existingItem->with_moa);
        $this->assertSame(2024, $existingItem->year_of_moa);
        $this->assertTrue($existingItem->with_res);
        $this->assertSame(2025, $existingItem->year_of_resolution);
        $this->assertTrue($existingItem->included_aip);
        $this->assertTrue($existingItem->with_adopted);
        $this->assertFalse($existingItem->with_replicated);
        $this->assertSame('dissolved', $existingItem->status);
        $this->assertSame('Seeder', $existingItem->createdby);
        $this->assertSame('Importer', $existingItem->updatedby);

        $this->assertDatabaseHas('region_items', [
            'region_id' => $region->id,
            'title' => 'Additional ST',
            'province' => 'Abra',
            'municipality' => 'La Paz',
            'with_replicated' => 1,
            'updatedby' => 'Importer',
        ]);
    }

    private function createGoogleSheetWorkbook(array $sheets): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($sheets as $index => [$sheetName, $dataRows]) {
            $sheet = $spreadsheet->createSheet($index);
            $sheet->setTitle($sheetName);
            $sheet->fromArray([
                [
                    'Title of ST',
                    'Province',
                    'Name of Municipality',
                    'With Expression of Interest',
                    'With MOA',
                    'Year of MOA',
                    'With Resolution',
                    'Year of Resolution',
                    'Included AIP',
                    'Adopted/Replicated',
                    'Status',
                ],
                array_fill(0, 11, ''),
                ...$dataRows,
            ], null, 'A1');
        }

        $spreadsheet->setActiveSheetIndex(0);

        $tempPath = tempnam(sys_get_temp_dir(), 'region-sheet-');
        if ($tempPath === false) {
            $this->fail('Failed to create a temporary workbook path.');
        }

        $xlsxPath = $tempPath . '.xlsx';
        rename($tempPath, $xlsxPath);

        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxPath);

        return $xlsxPath;
    }
}