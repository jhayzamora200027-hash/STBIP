<?php

namespace Tests\Unit;

use App\Services\RegionSheetImportService;
use PHPUnit\Framework\TestCase;

class RegionSheetImportServiceTest extends TestCase
{
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
}