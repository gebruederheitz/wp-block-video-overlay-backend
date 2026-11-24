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
        $attributes = $this->sanitizeAttributes($attributes);

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
                $userProvidedSlug = $attributes['ccLangPref'];
                $saneLangPrefSlug = $this->sanitizeCcLangPref(
                    $userProvidedSlug,
                );

                $query = Query::fromUri($url)
                    ->appendTo('cc_lang_pref', $saneLangPrefSlug)
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
            ? 'data-ghct-type="' . esc_attr($consentProviderType) . '"'
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

    /**
     * Reduces the attributes editors control to valid data for their type,
     * before filters and templates see them. Templates must still escape:
     * this only limits what an override that forgets to can print.
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function sanitizeAttributes(array $attributes): array
    {
        foreach (['videoUrl', 'videoEmbedUrl', 'mediaURL'] as $name) {
            if (array_key_exists($name, $attributes)) {
                $attributes[$name] = sanitize_url(
                    $this->toString($attributes[$name]),
                );
            }
        }

        if (array_key_exists('mediaID', $attributes)) {
            $attributes['mediaID'] = is_scalar($attributes['mediaID'])
                ? (int) $attributes['mediaID']
                : 0;
        }

        if (array_key_exists('className', $attributes)) {
            $classes =
                preg_split(
                    '/\s+/',
                    $this->toString($attributes['className']),
                    -1,
                    PREG_SPLIT_NO_EMPTY,
                ) ?:
                [];
            $attributes['className'] = implode(
                ' ',
                array_filter(
                    array_map('sanitize_html_class', $classes),
                    fn(string $class) => $class !== '',
                ),
            );
        }

        if (array_key_exists('type', $attributes)) {
            $attributes['type'] = sanitize_html_class(
                $this->toString($attributes['type']),
            );
        }

        // Consent service IDs are case-sensitive and not otherwise
        // restricted, so keep identifier characters instead of using
        // sanitize_key(), which lowercases.
        if (array_key_exists('providerType', $attributes)) {
            $attributes['providerType'] = (string) preg_replace(
                '/[^A-Za-z0-9_.:-]/',
                '',
                $this->toString($attributes['providerType']),
            );
        }

        return $attributes;
    }

    private function toString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function sanitizeCcLangPref(string $userProvidedSlug = ''): string
    {
        // WP built-in since 6.2.1, basically limits
        // a string to A-Z, a-z, 0-9, '_', '-'.
        return sanitize_locale_name($userProvidedSlug);
    }
}
