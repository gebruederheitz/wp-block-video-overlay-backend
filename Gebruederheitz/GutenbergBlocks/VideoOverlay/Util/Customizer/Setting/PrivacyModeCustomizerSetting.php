<?php

declare(strict_types=1);

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Setting;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Enum\PrivacyMode;
use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

/**
 * @extends BasicCustomizerSetting<'none'|'select'|'always'>
 */
class PrivacyModeCustomizerSetting extends BasicCustomizerSetting
{
    protected $default = PrivacyMode::NONE;

    protected ?string $inputType = 'select';

    /**
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        return [
            PrivacyMode::NONE => 'None',
            PrivacyMode::SELECT => 'Individually per block',
            PrivacyMode::ALWAYS => 'Always',
        ];
    }

    public function getKey(): string
    {
        return 'ghwp-video-overlay-enable-privacy-mode';
    }

    public function getLabel(): string
    {
        return 'Use Youtube privacy mode for video overlay blocks (only works for inline videos)';
    }
}
