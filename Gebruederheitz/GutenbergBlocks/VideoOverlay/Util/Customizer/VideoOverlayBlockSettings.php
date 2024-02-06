<?php

declare(strict_types=1);

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Enum\PrivacyMode;
use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Setting\PrivacyModeCustomizerSetting;
use Gebruederheitz\GutenbergBlocks\VideoOverlay\VideoOverlayBlock;
use Gebruederheitz\Wordpress\Customizer\AbstractCustomizerSettingsHandler;

class VideoOverlayBlockSettings extends AbstractCustomizerSettingsHandler
{
    /**
     * @return 'none'|'select'|'always'
     */
    public static function getPrivacyMode(): string
    {
        return PrivacyModeCustomizerSetting::getValue();
    }

    public static function register(): void
    {
        add_filter(
            VideoOverlayBlock::HOOK_URL_TEMPLATES,
            [self::class, 'onFilterUrlTemplates'],
            10,
            2,
        );
    }

    /**
     * @param array<string, array<string, string>> $templates
     * @param array<string, mixed> $attributes
     * @return array<string, array<string, string>>
     */
    public static function onFilterUrlTemplates(
        array $templates,
        array $attributes
    ): array {
        $privacyMode = self::getPrivacyMode();

        if ($privacyMode === PrivacyMode::ALWAYS) {
            self::replaceUrlTemplate($templates);
        } elseif ($privacyMode === PrivacyMode::SELECT) {
            $usePM = $attributes['usePrivacyMode'] ?? false;
            if ($usePM) {
                self::replaceUrlTemplate($templates);
            }
        }

        return $templates;
    }

    /**
     * @param array<string, array<string, string>> $templates
     */
    protected static function replaceUrlTemplate(array &$templates): void
    {
        $templates['youtube']['inline'] =
            'https://www.youtube-nocookie.com/embed/{videoId}';
    }

    /**
     * @inheritDoc
     */
    protected function getSettings(): array
    {
        return [new PrivacyModeCustomizerSetting()];
    }
}
