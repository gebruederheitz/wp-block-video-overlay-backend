<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Tests;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\VideoOverlayBlockHandler;
use PHPUnit\Framework\TestCase;

/**
 * Renders the block through VideoOverlayBlockHandler::renderBlock() and checks
 * the video URL handed to the template (load_template() is a recorder, see
 * tests/bootstrap.php).
 */
class VideoOverlayBlockHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        gh_test_reset_loaded_templates();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function renderedVideoUrl(array $attributes): string
    {
        $handler = new VideoOverlayBlockHandler(
            'ghwp/video-overlay',
            '/templates/video-overlay.php',
            [],
            ['videoUrl'],
        );

        $handler->renderBlock($attributes + [
            'type' => 'inline',
            'videoUrl' => 'https://www.youtube.com/embed/abc123',
            'videoId' => 'abc123',
            'videoProvider' => 'youtube',
            'providerType' => '',
        ]);

        $loaded = gh_test_loaded_templates();
        $this->assertCount(1, $loaded);

        return $loaded[0]['args']['videoUrl'];
    }

    public function testVideoUrlHasNoCaptionParametersWithoutCcLangPref(): void
    {
        $this->assertSame(
            'https://www.youtube.com/embed/abc123',
            $this->renderedVideoUrl([]),
        );
    }

    /**
     * Also guards against league/uri's #[Deprecated] methods: on PHP 8.4+ a
     * call to one raises a deprecation, which phpunit.xml.dist turns into an
     * error.
     */
    public function testCcLangPrefAddsCaptionParametersToTheVideoUrl(): void
    {
        $this->assertSame(
            'https://www.youtube.com/embed/abc123?cc_lang_pref=de-DE&cc_load_policy=1',
            $this->renderedVideoUrl(['ccLangPref' => 'de-DE']),
        );
    }

    public function testCcLangPrefIsSanitized(): void
    {
        $this->assertSame(
            'https://www.youtube.com/embed/abc123?cc_lang_pref=enGB&cc_load_policy=1',
            $this->renderedVideoUrl(['ccLangPref' => 'en"GB<']),
        );
    }
}
