<?php

declare(strict_types=1);

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay;

use Gebruederheitz\GutenbergBlocks\DynamicBlock;
use Gebruederheitz\GutenbergBlocks\PartialRenderer;
use League\Uri\Components\Query;
use League\Uri\Contracts\UriException;
use League\Uri\Contracts\UriInterface;
use League\Uri\UriTemplate;

class VideoOverlayBlockHandler extends DynamicBlock
{
    public const URL_TEMPLATES = [
        'youtube' => [
            'inline' => 'https://www.youtube.com/embed/{videoId}',
            'overlay' => 'https://www.youtube.com/watch?v={videoId}',
        ],
        'vimeo' => [
            'inline' => 'https://player.vimeo.com/video/{videoId}',
            'overlay' => 'https://player.vimeo.com/video/{videoId}',
        ],
    ];

    /**
     * @inheritDoc
     * @param array<string, mixed> $attributes
     * @throws UriException
     */
    public function renderBlock(array $attributes = [], string $content = '')
    {
        $attributes = apply_filters(
            DynamicBlock::HOOK_FILTER_BLOCK_TYPE_ATTRIBUTES . $this->name,
            $attributes,
        );

        if (!$this->requiredAttributesArePresent($attributes)) {
            return null;
        }

        $type = $attributes['type'];

        // Prepare the video URL
        if (isset($attributes['videoId'], $attributes['videoProvider'])) {
            // video ID and provider are set, so we process the urls
            $videoId = $attributes['videoId'];
            $provider = $attributes['videoProvider'];

            $templates = apply_filters(
                VideoOverlayBlock::HOOK_URL_TEMPLATES,
                self::URL_TEMPLATES,
                $attributes,
            );
            $template = new UriTemplate($templates[$provider][$type] ?? '');
            $url = $template->expand(['videoId' => $videoId]);

            if (!empty($attributes['ccLangPref'])) {
                $query = Query::createFromUri($url)
                    ->appendTo('cc_lang_pref', $attributes['ccLangPref'])
                    ->appendTo('cc_load_policy', '1');
                $url = $url->withQuery($query->__toString());
            }

            /** @var UriInterface $url */
            $url = apply_filters(
                VideoOverlayBlock::HOOK_VIDEO_URL,
                $url,
                $type,
                $provider,
                $attributes,
            );

            $attributes['videoUrl'] = (string) $url;
        } else {
            if ($type === 'inline' && !empty($attributes['videoEmbedUrl'])) {
                $attributes['videoUrl'] = $attributes['videoEmbedUrl'];
            }
        }

        // Prepare wrapper element class names
        $className = $attributes['className'] ?? '';
        $attributes['classNames'] = [
            $className,
            'ghwp-video',
            'ghwp-video--' . $type,
        ];

        $consentProviderType = $attributes['providerType'];

        // Prepare the source attribute's name (href, src, data-ghct-src etc.)
        $srcAttributeName = !empty($consentProviderType)
            ? 'data-ghct-src'
            : ($type === 'inline'
                ? 'src'
                : 'href');
        $attributes['sourceAttributeName'] = $srcAttributeName;

        // Prepare the additional consent management provider type attribute
        $consentProviderAttribute = !empty($consentProviderType)
            ? 'data-ghct-type="' . $consentProviderType . '"'
            : '';
        $attributes['consentProviderAttribute'] = $consentProviderAttribute;

        // Prepare the "play" icon template's path
        $attributes['playIconPath'] =
            __DIR__ . '/../../../templates/play-icon.php';

        $attributes['setWidthAndHeight'] = true;

        // Filter attributes before rendering
        $attributes = apply_filters(
            VideoOverlayBlock::HOOK_RENDER_ATTRIBUTES,
            $attributes,
            $type,
        );

        // Filter the template partial path
        $partial = apply_filters(
            VideoOverlayBlock::HOOK_TEMPLATE_PARTIAL,
            $this->partial,
            $type,
            $attributes,
        );

        return PartialRenderer::render(
            $partial,
            $attributes,
            $content,
            $this->templateOverridePath,
        );
    }
}
