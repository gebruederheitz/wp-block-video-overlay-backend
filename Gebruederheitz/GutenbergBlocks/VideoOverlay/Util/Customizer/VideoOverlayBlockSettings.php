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

    public function __construct()
    {
        parent::__construct();
        add_filter(
            VideoOverlayBlock::HOOK_URL_TEMPLATES,
            [$this, 'onFilterUrlTemplates'],
            10,
            2,
        );
    }

    /**
     * @inheritDoc
     */
    protected function getSettings(): array
    {
        return [new PrivacyModeCustomizerSetting()];
    }

    /**
     * @param array<string, array<string, string>> $templates
     * @param array<string, mixed> $attributes
     * @return array<string, array<string, string>>
     */
    public function onFilterUrlTemplates(
        array $templates,
        array $attributes
    ): array {
        $privacyMode = self::getPrivacyMode();

        if ($privacyMode === PrivacyMode::ALWAYS) {
            $this->replaceUrlTemplate($templates);
        } elseif ($privacyMode === PrivacyMode::SELECT) {
            $usePM = $attributes['usePrivacyMode'] ?? false;
            if ($usePM) {
                $this->replaceUrlTemplate($templates);
            }
        }

        return $templates;
    }

    /**
     * @param array<string, array<string, string>> $templates
     */
    protected function replaceUrlTemplate(array &$templates): void
    {
        $templates['youtube']['inline'] =
            'https://www.youtube-nocookie.com/embed/{videoId}';
    }
}
