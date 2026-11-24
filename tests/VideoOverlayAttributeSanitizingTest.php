<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Tests;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\VideoOverlayBlockHandler;
use PHPUnit\Framework\TestCase;

/**
 * VideoOverlayBlockHandler sanitises block attributes before filters and
 * templates see them, so template overrides that print a value unescaped
 * can't be injected through it. Templates must still escape.
 *
 * The template path doesn't exist, so load_template() only records the data
 * (see tests/bootstrap.php).
 */
class VideoOverlayAttributeSanitizingTest extends TestCase
{
    protected function tearDown(): void
    {
        gh_test_reset_loaded_templates();
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>|null The template's data, null if the block wasn't rendered
     */
    private function templateData(array $attributes): ?array
    {
        gh_test_reset_loaded_templates();

        $handler = new VideoOverlayBlockHandler(
            'ghwp/video-overlay',
            '/does-not-exist/video-overlay.php',
            [],
            ['videoUrl'],
        );

        $handler->renderBlock($attributes + [
            'type' => 'overlay',
            'videoUrl' => 'https://www.youtube.com/watch?v=abc123',
            'mediaURL' => 'https://example.com/preview.jpg',
            'providerType' => '',
        ]);

        $loaded = gh_test_loaded_templates();

        return $loaded === [] ? null : $loaded[0]['args'];
    }

    public function testVideoUrlLosesQuotesAndWhitespace(): void
    {
        $data = $this->templateData(['videoUrl' => 'https://example.com/x" onfocus="alert(1)']);

        $this->assertSame('https://example.com/x%20onfocus=alert(1)', $data['videoUrl']);
    }

    public function testBlockWithAScriptVideoUrlIsNotRendered(): void
    {
        $this->assertNull($this->templateData(['videoUrl' => 'javascript:alert(1)']));
    }

    public function testScriptVideoEmbedUrlIsDropped(): void
    {
        $data = $this->templateData([
            'type' => 'inline',
            'videoEmbedUrl' => 'javascript:alert(1)',
        ]);

        $this->assertSame('', $data['videoEmbedUrl']);
        $this->assertSame('https://www.youtube.com/watch?v=abc123', $data['videoUrl']);
    }

    public function testInlineVideoUrlComesFromTheSanitisedVideoEmbedUrl(): void
    {
        $data = $this->templateData([
            'type' => 'inline',
            'videoEmbedUrl' => 'https://example.com/embed" onload="alert(1)',
        ]);

        $this->assertSame('https://example.com/embed%20onload=alert(1)', $data['videoUrl']);
    }

    public function testMediaUrlLosesQuotesAndScriptSchemes(): void
    {
        $this->assertSame(
            'https://example.com/a.jpg%20onerror=alert(1)',
            $this->templateData(['mediaURL' => 'https://example.com/a.jpg" onerror="alert(1)'])['mediaURL'],
        );
        $this->assertSame(
            '',
            $this->templateData(['mediaURL' => 'javascript:alert(1)'])['mediaURL'],
        );
    }

    public function testMediaIdIsAnInteger(): void
    {
        $this->assertSame(1, $this->templateData(['mediaID' => '1" onerror="alert(1)'])['mediaID']);
    }

    public function testClassNameKeepsOnlyClassCharacters(): void
    {
        $data = $this->templateData(['className' => 'is-style-wide x" onfocus="alert(1)']);

        $this->assertSame('is-style-wide x onfocusalert1', $data['className']);
        $this->assertSame('is-style-wide x onfocusalert1', $data['classNames'][0]);
    }

    public function testTypeKeepsOnlyClassCharacters(): void
    {
        $data = $this->templateData(['type' => 'overlay" onfocus="alert(1)']);

        $this->assertSame('overlayonfocusalert1', $data['type']);
        $this->assertSame('ghwp-video--overlayonfocusalert1', $data['classNames'][2]);
    }

    public function testProviderTypeKeepsOnlyIdentifierCharacters(): void
    {
        $data = $this->templateData(['providerType' => 'youtube" onfocus="alert(1)']);

        $this->assertSame('youtubeonfocusalert1', $data['providerType']);
        $this->assertSame('data-ghct-type="youtubeonfocusalert1"', $data['consentProviderAttribute']);
    }

    public function testNormalValuesAreUnchanged(): void
    {
        $attributes = [
            'type' => 'inline',
            'videoUrl' => 'https://player.vimeo.com/video/123?h=abc&dnt=1',
            'videoEmbedUrl' => 'https://player.vimeo.com/video/123?h=abc&dnt=1',
            'mediaURL' => '/wp-content/uploads/2026/11/preview-1.jpg',
            'mediaID' => 42,
            'mediaAltText' => 'It\'s a "preview" <image>',
            'className' => 'is-style-wide my_class',
            'providerType' => 'Foo.Bar:baz_qux-1',
        ];

        $data = $this->templateData($attributes);

        foreach ($attributes as $name => $value) {
            $this->assertSame($value, $data[$name], $name);
        }
    }
}
