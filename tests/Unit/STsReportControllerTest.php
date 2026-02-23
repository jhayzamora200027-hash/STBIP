<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\STsReportController;

class STsReportControllerTest extends TestCase
{
    /**
     * Helper to invoke private/protected inferRegionCodeFromRow
     */
    protected function infer(string $regionText)
    {
        $ctrl = new STsReportController();
        $method = new \ReflectionMethod(STsReportController::class, 'inferRegionCodeFromRow');
        $method->setAccessible(true);
        return $method->invoke($ctrl, ['region' => $regionText]);
    }

    public function testKnownAliases()
    {
        $this->assertEquals('Region I', $this->infer('Ilocos Region'));
        $this->assertEquals('Region II', $this->infer('Cagayan Valley STs'));
        $this->assertEquals('NCR', $this->infer('National Capital Region'));
        $this->assertEquals('Region IV-A', $this->infer('CALABARZON  '));
        $this->assertEquals('Region IV-B', $this->infer('MIMAROPA'));        
        $this->assertEquals('BARMM', $this->infer('Bangsamoro Autonomous Region in Muslim Mindanao'));        
        $this->assertEquals('CARAGA', $this->infer('some CARAGA data'));        
        $this->assertEquals('CAR', $this->infer('CAR'));        
    }

    public function testRomanNumeralFallback()
    {
        // pure number strings are mapped to canonical region names
        $this->assertEquals('Region V', $this->infer('5'));

        // roman numerals embedded in text should also work
        $this->assertEquals('Region IX', $this->infer('Region IX (Zamboanga)'));
        $this->assertEquals('Region X', $this->infer('X'));
        $this->assertEquals('Region IV-A', $this->infer('IV-A'));
        $this->assertEquals('Region IV-B', $this->infer('Region ivb'));
    }

    public function testFallbackRawPreserved()
    {
        // string not matched by any pattern should simply return itself trimmed
        $this->assertEquals('FooBar', $this->infer('FooBar'));
        $this->assertEquals('region 14', $this->infer(' region 14 '));
        $this->assertNull($this->infer(''));
    }

    public function testAjaxRegionHierarchyDropsBlankTitles()
    {
        // bootstrap the application container to satisfy response() calls
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // stub out getAllData to return our rows
        $stub = $this->getMockBuilder(STsReportController::class)
                     ->onlyMethods(['getAllData'])
                     ->getMock();
        $stub->method('getAllData')->willReturn([
            'data' => [
                // row with title
                ['region' => 'Test Region', 'province' => 'P', 'municipality' => 'C', 'title' => 'HasTitle'],
                // row with whitespace only – should be removed
                ['region' => 'Test Region', 'province' => 'P', 'municipality' => 'C', 'title' => '   '],
                // row containing control characters that clean() will strip
                ['region' => 'Test Region', 'province' => 'P', 'municipality' => "C\x03", 'title' => "Foo\x0B"],
                // completely empty row - should also be removed
                ['region' => 'Test Region', 'province' => '', 'municipality' => '', 'title' => ''],
            ],
            'regions' => ['Test Region'],
        ]);

        $req = \Illuminate\Http\Request::create('/','GET', ['region_image' => 'Test Region']);
        $response = $stub->ajaxRegionHierarchy($req);
        $json = json_decode($response->getContent(), true);
        $this->assertCount(2, $json['allRows']); // ones with stripped title and control chars remain after cleanup
        $this->assertEquals(['P'], $json['provinces']);
    }

    public function testBuildHierarchyHandlesUnknowns()
    {
        $ctrl = new STsReportController();
        $rows = [
            ['province'=>'', 'municipality'=>'','title'=>''],
            ['province'=>'Foo','municipality'=>'Bar','title'=>'Baz'],
        ];
        $ref = new \ReflectionMethod(STsReportController::class, 'buildHierarchy');
        $ref->setAccessible(true);
        list($provs, $grouped) = $ref->invoke($ctrl, $rows);
        $this->assertEquals(['UNKNOWN','Foo'], $provs);
        $this->assertArrayHasKey('UNKNOWN', $grouped);
        $this->assertArrayHasKey('UNKNOWN', $grouped['UNKNOWN']);
        $this->assertArrayHasKey('Bar', $grouped['Foo']);
    }
}
