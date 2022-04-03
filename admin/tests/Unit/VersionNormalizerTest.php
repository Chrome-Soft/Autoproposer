<?php
/**
 * Created by Chrome-Soft Kft.
 * User: developer
 */

namespace Tests\Unit;

use App\Product;
use App\Services\ProductAttributeSyncer;
use App\Services\Segment\ExpressionNormalizer\VersionNormalizer;
use Tests\TestCase;

class VersionNormalizerTest extends TestCase
{
    /** @test */
    public function it_converts_version_numbers_to_semantic_format()
    {
        $normalizer = new VersionNormalizer('os_version', '>=', 'where', '12');
        $version = $this->invokeMethod($normalizer, 'convertVersionToSemantic');

        $this->assertEquals('12.0.0', $version);

        $normalizer = new VersionNormalizer('os_version', '>=','where', '12.13');
        $version = $this->invokeMethod($normalizer, 'convertVersionToSemantic');

        $this->assertEquals('12.13.0', $version);

        $normalizer = new VersionNormalizer('os_version', '>=', 'where', '12.13.9');
        $version = $this->invokeMethod($normalizer, 'convertVersionToSemantic');

        $this->assertEquals('12.13.9', $version);

        $normalizer = new VersionNormalizer('os_version', '>=', 'where', '12.13.9.1');
        $version = $this->invokeMethod($normalizer, 'convertVersionToSemantic');

        $this->assertEquals('12.13.9', $version);

        $normalizer = new VersionNormalizer('os_version', '>=', 'where', '7.1.');
        $version = $this->invokeMethod($normalizer, 'convertVersionToSemantic');

        $this->assertEquals('7.1.0', $version);

        $normalizer = new VersionNormalizer('os_version', '>=', 'where', '7.1.2.3.4.5.......');
        $version = $this->invokeMethod($normalizer, 'convertVersionToSemantic');

        $this->assertEquals('7.1.2', $version);
    }
}
