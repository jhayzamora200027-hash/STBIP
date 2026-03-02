<?php

namespace Tests\Unit;

use App\Http\Controllers\STsReportController;
use PHPUnit\Framework\TestCase;

class RegionNormalizationTest extends TestCase
{
    /**
     * Controller helper is private; use reflection to invoke it for testing.
     */
    private function infer(string $region): ?string
    {
        $ctrl = new STsReportController();
        $ref = new \ReflectionMethod($ctrl, 'inferRegionCodeFromRow');
        $ref->setAccessible(true);
        return $ref->invoke($ctrl, ['region' => $region]);
    }

    public function testCalabarzonAliasMapsToRegion4A()
    {
        $this->assertSame('Region IV-A', $this->infer('Calabarzon'));   // standard name
        $this->assertSame('Region IV-A', $this->infer('CALABARZON'));   // uppercase
        $this->assertSame('Region IV-A', $this->infer('calborazon'));   // common typo
        $this->assertSame('Region IV-A', $this->infer('CALBORAZON'));   // uppercase typo
    }

    public function testNumericRomanFallbackStillWorks()
    {
        $this->assertSame('Region IV-A', $this->infer('4')); // numeric sheet
        $this->assertSame('Region IV-A', $this->infer('IV-A')); // roman string
    }
}
