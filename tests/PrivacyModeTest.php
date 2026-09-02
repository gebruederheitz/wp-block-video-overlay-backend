<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Tests;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Enum\PrivacyMode;
use PHPUnit\Framework\TestCase;

class PrivacyModeTest extends TestCase
{
    public function testConstantsHaveExpectedStringValues(): void
    {
        $this->assertSame('none', PrivacyMode::NONE);
        $this->assertSame('select', PrivacyMode::SELECT);
        $this->assertSame('always', PrivacyMode::ALWAYS);
    }

    public function testConstantsAreMutuallyDistinct(): void
    {
        $values = [PrivacyMode::NONE, PrivacyMode::SELECT, PrivacyMode::ALWAYS];

        $this->assertCount(3, array_unique($values));
    }
}
