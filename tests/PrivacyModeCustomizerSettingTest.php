<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Tests;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Enum\PrivacyMode;
use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Setting\PrivacyModeCustomizerSetting;
use PHPUnit\Framework\TestCase;

class PrivacyModeCustomizerSettingTest extends TestCase
{
    public function testKeyAndDefaultAreStable(): void
    {
        $setting = PrivacyModeCustomizerSetting::get();

        $this->assertSame(
            'ghwp-video-overlay-enable-privacy-mode',
            $setting->getKey(),
        );
        $this->assertSame(PrivacyMode::NONE, $setting->getDefault());
        $this->assertSame('select', $setting->getInputType());
    }

    public function testOptionsCoverAllPrivacyModes(): void
    {
        $setting = PrivacyModeCustomizerSetting::get();

        $this->assertSame(
            [
                PrivacyMode::NONE => 'None',
                PrivacyMode::SELECT => 'Individually per block',
                PrivacyMode::ALWAYS => 'Always',
            ],
            $setting->getOptions(),
        );
    }
}
