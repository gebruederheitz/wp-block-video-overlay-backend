<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay;

use Gebruederheitz\GutenbergBlocks\BlockRegistrar;
use Gebruederheitz\GutenbergBlocks\DynamicBlock;
use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\Enum\PrivacyMode;
use Gebruederheitz\GutenbergBlocks\VideoOverlay\Util\Customizer\VideoOverlayBlockSettings;
use Gebruederheitz\Wordpress\Rest\Traits\withREST;

class VideoOverlayBlock
{
    /*
     * @NB workaround for a truly weird bug, where withRest can not be found
     * in ImageSideloader if it has not been used elsewhere before
     */
    use withREST;

    public const BLOCK_NAME = 'ghwp/video-overlay';

    /**
     * @hook ghwp-embed-types
     * @description An array of possible embed types for use with a consent
     *              management solution.
     */
    public const HOOK_EMBED_TYPES = 'ghwp-embed-types';

    public const HOOK_CC_LANG_PREFS = 'ghwp-cc-lang-prefs';

    public const HOOK_ATTRIBUTES = 'ghwp-video-overlay-attributes';

    public const HOOK_VIDEO_URL = 'ghwp-video-overlay-video-url';

    public const HOOK_TEMPLATE_PARTIAL = 'ghwp-video-overlay-template-partial';

    public const HOOK_URL_TEMPLATES = 'ghwp-video-overlay-url-templates';

    public const HOOK_RENDER_ATTRIBUTES = 'ghwp-video-overlay-render-attributes';

    /** @var DynamicBlock */
    protected $blockHandler;

    protected const ATTRIBUTES = [
        'videoId' => [
            'type' => 'string',
            'default' => null,
        ],
        'videoProvider' => [
            'type' => 'string',
            'default' => null,
        ],
        'videoUrl' => [
            'type' => 'string',
            'default' => null,
        ],
        'videoEmbedUrl' => [
            'type' => 'string',
            'default' => '',
        ],
        'mediaURL' => [
            'type' => 'string',
            'default' => '',
        ],
        'mediaID' => [
            'type' => 'number',
            'default' => 0,
        ],
        'mediaAltText' => [
            'type' => 'string',
            'default' => '',
        ],
        'providerType' => [
            'type' => 'string',
            'default' => 'youtube',
        ],
        'type' => [
            'type' => 'string',
            'default' => 'overlay',
        ],
        'lazyLoadPreviewImage' => [
            'type' => 'boolean',
            'default' => true,
        ],
        'usePrivacyMode' => [
            'type' => 'boolean',
            'default' => false,
        ],
    ];

    protected const REQUIRED_ATTRIBUTES = ['videoUrl'];

    /**
     * @param string $defaultEmbedProvider The embed provider type used by default
     */
    public function __construct(string $defaultEmbedProvider = 'youtube')
    {
        // Make sure an instance of BlockRegistrar exists
        BlockRegistrar::getInstance();
        new ImageSideloader();
        $this->blockHandler = new VideoOverlayBlockHandler(
            self::BLOCK_NAME,
            __DIR__ . '/../../../templates/video-overlay.php',
            $this->getAttributes($defaultEmbedProvider),
            self::REQUIRED_ATTRIBUTES,
            'template-parts/blocks/video-overlay.php',
        );
        $this->blockHandler->register();

        add_filter(BlockRegistrar::HOOK_SCRIPT_LOCALIZATION_DATA, [
            $this,
            'onScriptLocalizationData',
        ]);
    }

    /* @NB: part of the same fix: abstract methods must be implemented */
    /**
     * @return array<array<string, mixed>>
     */
    public static function getRestRoutes(): array
    {
        return [];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getInstanceRestRoutes(): array
    {
        return [];
    }

    /**
     * Provide the editor component with relevant data through the script
     * localization API.
     *
     * @param array<string, mixed> $locDat
     *
     * @return array<string, mixed>
     */
    public function onScriptLocalizationData(array $locDat): array
    {
        if (!isset($locDat['restCustomUrl'])) {
            $locDat['restCustomUrl'] = get_rest_url(
                null,
                withRest::getRestNamespace(),
            );
        }
        if (!isset($locDat['restApiNonce'])) {
            $locDat['restApiNonce'] = wp_create_nonce('wp_rest');
        }
        if (!isset($locDat['embedTypes'])) {
            $locDat['embedTypes'] =
                apply_filters(static::HOOK_EMBED_TYPES, []) ?: [];
        }
        if (!isset($locDat['ccLangPrefs'])) {
            $locDat['ccLangPrefs'] =
                apply_filters(static::HOOK_CC_LANG_PREFS, []) ?: [];
        }
        $locDat['privacyModeOption'] =
            VideoOverlayBlockSettings::getPrivacyMode() === PrivacyMode::SELECT
                ? 'true'
                : 'false';

        return $locDat;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAttributes(string $defaultEmbedProvider): array
    {
        $attributes = self::ATTRIBUTES;
        $attributes['providerType']['default'] = $defaultEmbedProvider;
        $attributes = apply_filters(self::HOOK_ATTRIBUTES, $attributes);

        return $attributes;
    }
}
