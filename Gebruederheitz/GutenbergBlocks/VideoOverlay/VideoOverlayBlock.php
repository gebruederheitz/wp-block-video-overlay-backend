<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay;

use Gebruederheitz\GutenbergBlocks\BlockRegistrar;
use Gebruederheitz\GutenbergBlocks\DynamicBlock;
use Gebruederheitz\Traits\Rest\withREST;

class VideoOverlayBlock
{
    /*
     * @NB workaround for a truly weird bug, where withRest ca not be found
     * in ImageSideloader if it has not been used elsewhere before
     */
    use withREST;
    /**
     * @hook ghwp-embed-types
     * @description An array of possible embed types for use with a consent
     *              management solution.
     */
    public const HOOK_EMBED_TYPES = 'ghwp-embed-types';

    /** @var DynamicBlock */
    protected $blockHandler;

    protected const ATTRIBUTES = [
        'videoUrl'     => [
            'type'    => 'string',
            'default' => null,
        ],
        'mediaURL'     => [
            'type'    => 'string',
            'default' => '',
        ],
        'mediaID'      => [
            'type'    => 'number',
            'default' => 0,
        ],
        'mediaAltText' => [
            'type'    => 'string',
            'default' => '',
        ],
        'providerType' => [
            'type'    => 'string',
            'default' => 'youtube',
        ],
    ];

    protected const REQUIRED_ATTRIBUTES = ['videoUrl'];

    public function __construct()
    {
        BlockRegistrar::getInstance();
        new ImageSideloader();
        $this->blockHandler = new DynamicBlock(
            'ghwp/video-overlay',
            __DIR__ . '/../../../templates/video-overlay.php',
            self::ATTRIBUTES,
            self::REQUIRED_ATTRIBUTES,
            'template-parts/blocks/video-overlay.php'
        );
        $this->blockHandler->register();

        add_filter(
            BlockRegistrar::HOOK_SCRIPT_LOCALIZATION_DATA,
            [$this, 'onScriptLocalizationData']
        );
    }

    /* @NB: part of the same fix: abstract methods must be implemented */
    public static function getRestRoutes(): array {return [];}

    public function getInstanceRestRoutes(): array {return [];}

    /**
     * Provide the editor component with relevant data through the script
     * localization API.
     *
     * @param array $locDat
     *
     * @return array
     */
    public function onScriptLocalizationData(array $locDat): array
    {
        if (!isset($locDat['restCustomUrl'])) {
            $locDat['restCustomUrl'] = get_rest_url(null, withRest::getRestNamespace());
        }
        if (!isset($locDat['restApiNonce'])) {
            $locDat['restApiNonce'] = wp_create_nonce('wp_rest');
        }
        if (!isset($locDat['embedTypes'])) {
            $locDat['embedTypes'] = apply_filters(static::HOOK_EMBED_TYPES, []) ?: [];
        }


        return $locDat;
    }
}
