<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Tests;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Gebruederheitz\GutenbergBlocks\PartialRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Renders the default template (templates/video-overlay.php) with raw values
 * and checks the HTML structure, so attribute values can't inject elements,
 * attributes or script URLs.
 *
 * The template is rendered directly, with data shaped like the handler's,
 * because VideoOverlayBlockHandler sanitises attributes first (see
 * VideoOverlayAttributeSanitizingTest). This tests the template's own escaping.
 *
 * esc_url() and esc_attr() are stubs modelled on WordPress core (see
 * tests/bootstrap.php): these tests pin that every value is escaped for its
 * context, not WordPress's escaping itself.
 */
class VideoOverlayTemplateTest extends TestCase
{
    /** Attribute names allowed per element; anything else is injected. */
    private const ALLOWED_ATTRIBUTES = [
        'div' => ['class', 'style'],
        'a' => ['class', 'href', 'data-ghct-src', 'data-ghct-type'],
        'iframe' => ['src', 'data-ghct-src', 'data-ghct-type'],
        'img' => ['width', 'height', 'loading', 'src', 'alt', 'class'],
        'svg' => ['class', 'xmlns', 'width', 'height', 'viewbox'],
        'path' => ['d'],
        'polygon' => ['points'],
    ];

    private const URL_ATTRIBUTES = ['href', 'src', 'data-ghct-src'];

    private const BREAKOUT = '" onfocus="alert(1)" x="';

    protected function tearDown(): void
    {
        gh_test_reset_loaded_templates();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function render(array $attributes): DOMXPath
    {
        $type = $attributes['type'];

        $html = PartialRenderer::render(
            __DIR__ . '/../templates/video-overlay.php',
            $attributes + [
                'videoUrl' => 'https://www.youtube.com/watch?v=abc123',
                'mediaURL' => 'https://example.com/preview.jpg',
                'mediaAltText' => 'Preview',
                'classNames' => [
                    $attributes['className'] ?? '',
                    'ghwp-video',
                    'ghwp-video--' . $type,
                ],
                'sourceAttributeName' => $type === 'inline' ? 'src' : 'href',
                'consentProviderAttribute' => '',
                'playIconPath' => __DIR__ . '/../templates/play-icon.php',
                'setWidthAndHeight' => true,
            ],
        );
        $this->assertIsString($html);

        $document = new DOMDocument();
        $document->loadHTML(
            '<?xml encoding="UTF-8"><body>' . $html . '</body>',
            LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        return new DOMXPath($document);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function maliciousAttributes(): array
    {
        $rows = [];
        foreach (['overlay', 'inline'] as $type) {
            $rows += [
                "$type: videoUrl breaks out of its attribute" => [
                    ['type' => $type, 'videoUrl' => 'https://example.com/x' . self::BREAKOUT],
                ],
                "$type: videoUrl is a javascript: URL" => [
                    ['type' => $type, 'videoUrl' => 'javascript:alert(1)'],
                ],
                "$type: videoUrl is an entity-encoded javascript: URL" => [
                    ['type' => $type, 'videoUrl' => 'javascript&#58;alert(1)'],
                ],
                "$type: mediaURL breaks out of its attribute" => [
                    ['type' => $type, 'mediaURL' => 'https://example.com/a.jpg' . self::BREAKOUT],
                ],
                "$type: mediaURL breaks out of a single-quoted attribute" => [
                    ['type' => $type, 'mediaURL' => "https://example.com/a.jpg' onfocus='alert(1)"],
                ],
                "$type: mediaURL is a javascript: URL" => [
                    ['type' => $type, 'mediaURL' => 'javascript:alert(1)'],
                ],
                "$type: className breaks out of its attribute" => [
                    ['type' => $type, 'className' => 'x' . self::BREAKOUT],
                ],
                "$type: className injects an element" => [
                    ['type' => $type, 'className' => '"><script>alert(1)</script>'],
                ],
            ];
        }

        return $rows + [
            'inline: mediaURL breaks out of the CSS url()' => [
                ['type' => 'inline', 'mediaURL' => 'https://example.com/a.jpg");background:url("https://evil.example/x'],
            ],
            'overlay: mediaAltText breaks out of its attribute' => [
                ['type' => 'overlay', 'mediaAltText' => 'x' . self::BREAKOUT],
            ],
            'overlay: mediaAltText injects an element' => [
                ['type' => 'overlay', 'mediaAltText' => '"><script>alert(1)</script>'],
            ],
            'overlay: mediaID breaks out of the image class' => [
                ['type' => 'overlay', 'mediaID' => '1' . self::BREAKOUT],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    #[DataProvider('maliciousAttributes')]
    public function testAttributeValuesCannotInjectMarkup(array $attributes): void
    {
        $xpath = $this->render($attributes);

        /** @var DOMElement $element */
        foreach ($xpath->query('//body//*') as $element) {
            $tag = $element->tagName;
            $this->assertArrayHasKey($tag, self::ALLOWED_ATTRIBUTES, "Unexpected <$tag> element");

            foreach ($element->attributes as $attribute) {
                $name = $attribute->nodeName;
                $this->assertContains($name, self::ALLOWED_ATTRIBUTES[$tag], "Unexpected attribute $name on <$tag>");

                if (in_array($name, self::URL_ATTRIBUTES, true)) {
                    $this->assertDoesNotMatchRegularExpression(
                        '/^\s*javascript:/i',
                        $attribute->nodeValue,
                        "Script URL in $name on <$tag>",
                    );
                }
                if ($name === 'style') {
                    $this->assertMatchesRegularExpression(
                        '/^background-image: url\("[^"\\\\\\\\]*"\);$/',
                        $attribute->nodeValue,
                    );
                }
            }
        }
    }

    public function testOverlayOutputIsUnchangedForNormalValues(): void
    {
        $xpath = $this->render([
            'type' => 'overlay',
            'className' => 'is-style-wide',
            'videoUrl' => 'https://www.youtube.com/watch?v=abc123&t=10',
            'sourceAttributeName' => 'data-ghct-src',
            'consentProviderAttribute' => 'data-ghct-type="youtube"',
            'mediaID' => 42,
        ]);

        $wrapper = $xpath->query('//body/div')->item(0);
        $this->assertInstanceOf(DOMElement::class, $wrapper);
        $this->assertSame('is-style-wide ghwp-video ghwp-video--overlay', $wrapper->getAttribute('class'));

        $link = $xpath->query('//a')->item(0);
        $this->assertInstanceOf(DOMElement::class, $link);
        $this->assertSame('https://www.youtube.com/watch?v=abc123&t=10', $link->getAttribute('data-ghct-src'));
        $this->assertSame('youtube', $link->getAttribute('data-ghct-type'));

        $image = $xpath->query('//a/img')->item(0);
        $this->assertInstanceOf(DOMElement::class, $image);
        $this->assertSame('https://example.com/preview.jpg', $image->getAttribute('src'));
        $this->assertSame('Preview', $image->getAttribute('alt'));
        $this->assertSame('ghwp-video__thumb wp-image-42', $image->getAttribute('class'));
    }

    public function testInlineOutputIsUnchangedForNormalValues(): void
    {
        $xpath = $this->render([
            'type' => 'inline',
            'videoUrl' => 'https://player.vimeo.com/video/123?h=abc&dnt=1',
        ]);

        $container = $xpath->query('//div[@class="ghwp-video-image"]')->item(0);
        $this->assertInstanceOf(DOMElement::class, $container);
        $this->assertSame(
            'background-image: url("https://example.com/preview.jpg");',
            $container->getAttribute('style'),
        );

        $iframe = $xpath->query('//iframe')->item(0);
        $this->assertInstanceOf(DOMElement::class, $iframe);
        $this->assertSame('https://player.vimeo.com/video/123?h=abc&dnt=1', $iframe->getAttribute('src'));
    }
}
