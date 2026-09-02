<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Tests;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Enum\PrivacyMode;
use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\VideoOverlayBlockSettings;
use PHPUnit\Framework\TestCase;

class VideoOverlayBlockSettingsTest extends TestCase
{
    protected function tearDown(): void
    {
        gh_test_reset_theme_mods();
    }

    public function testGetPrivacyModeDefaultsToNone(): void
    {
        $this->assertSame(PrivacyMode::NONE, VideoOverlayBlockSettings::getPrivacyMode());
    }

    public function testGetPrivacyModeReflectsThemeMod(): void
    {
        gh_test_set_theme_mod(
            'ghwp-video-overlay-enable-privacy-mode',
            PrivacyMode::ALWAYS,
        );

        $this->assertSame(PrivacyMode::ALWAYS, VideoOverlayBlockSettings::getPrivacyMode());
    }

    public function testOnFilterUrlTemplatesLeavesTemplatesUntouchedWhenModeIsNone(): void
    {
        $templates = ['youtube' => ['inline' => 'https://www.youtube.com/embed/{videoId}']];

        $result = VideoOverlayBlockSettings::onFilterUrlTemplates($templates, []);

        $this->assertSame(
            'https://www.youtube.com/embed/{videoId}',
            $result['youtube']['inline'],
        );
    }

    public function testOnFilterUrlTemplatesReplacesInlineUrlWhenModeIsAlways(): void
    {
        gh_test_set_theme_mod(
            'ghwp-video-overlay-enable-privacy-mode',
            PrivacyMode::ALWAYS,
        );
        $templates = ['youtube' => ['inline' => 'https://www.youtube.com/embed/{videoId}']];

        $result = VideoOverlayBlockSettings::onFilterUrlTemplates($templates, []);

        $this->assertSame(
            'https://www.youtube-nocookie.com/embed/{videoId}',
            $result['youtube']['inline'],
        );
    }

    public function testOnFilterUrlTemplatesOnlyReplacesWhenModeIsSelectAndAttributeIsSet(): void
    {
        gh_test_set_theme_mod(
            'ghwp-video-overlay-enable-privacy-mode',
            PrivacyMode::SELECT,
        );
        $templates = ['youtube' => ['inline' => 'https://www.youtube.com/embed/{videoId}']];

        $untouched = VideoOverlayBlockSettings::onFilterUrlTemplates($templates, []);
        $this->assertSame(
            'https://www.youtube.com/embed/{videoId}',
            $untouched['youtube']['inline'],
        );

        $replaced = VideoOverlayBlockSettings::onFilterUrlTemplates($templates, [
            'usePrivacyMode' => true,
        ]);
        $this->assertSame(
            'https://www.youtube-nocookie.com/embed/{videoId}',
            $replaced['youtube']['inline'],
        );
    }
}
