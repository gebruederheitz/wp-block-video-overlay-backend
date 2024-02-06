<?php

declare(strict_types=1);

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Setting;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Enum\PrivacyMode;
use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

class PrivacyModeCustomizerSetting extends BasicCustomizerSetting
{
    protected static $key = 'ghwp-video-overlay-enable-privacy-mode';

    /** @var string $default */
    protected static $default = PrivacyMode::NONE;

    protected static $label = 'Use Youtube privacy mode for video overlay blocks (only works for inline videos)';

    protected static $inputType = 'select';

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
}
