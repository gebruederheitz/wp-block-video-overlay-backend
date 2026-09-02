<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay\Tests;

use Gebruederheitz\GutenbergBlocks\VideoOverlay\VideoOverlayBlock;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * These tests inspect VideoOverlayBlock's attribute definition via
 * reflection rather than instantiating the class: the real constructor
 * pulls in BlockRegistrar, ImageSideloader (which requires WP core admin
 * includes) and several WordPress hook functions, which would need heavy
 * WP mocking for no additional coverage of the block's actual attribute
 * contract.
 */
class VideoOverlayBlockDefaultsTest extends TestCase
{
    public function testBlockNameIsNamespaced(): void
    {
        $this->assertSame('ghwp/video-overlay', VideoOverlayBlock::BLOCK_NAME);
    }

    public function testRequiredAttributesIncludeVideoUrl(): void
    {
        $reflection = new ReflectionClass(VideoOverlayBlock::class);

        $this->assertSame(
            ['videoUrl'],
            $reflection->getConstant('REQUIRED_ATTRIBUTES'),
        );
    }

    public function testAttributeDefinitionHasExpectedDefaults(): void
    {
        $reflection = new ReflectionClass(VideoOverlayBlock::class);
        $attributes = $reflection->getConstant('ATTRIBUTES');

        $this->assertSame('youtube', $attributes['providerType']['default']);
        $this->assertSame('overlay', $attributes['type']['default']);
        $this->assertTrue($attributes['lazyLoadPreviewImage']['default']);
        $this->assertFalse($attributes['usePrivacyMode']['default']);
        $this->assertSame('', $attributes['videoEmbedUrl']['default']);
        $this->assertSame(0, $attributes['mediaID']['default']);
    }

    public function testGetAttributesAppliesDefaultEmbedProviderOverride(): void
    {
        $reflection = new ReflectionClass(VideoOverlayBlock::class);
        $method = $reflection->getMethod('getAttributes');
        $method->setAccessible(true);

        // getAttributes() is an instance method but only reads the
        // ATTRIBUTES constant and calls apply_filters() (stubbed as a
        // passthrough), so it is safe to invoke without a constructed
        // instance's side effects.
        $instance = $reflection->newInstanceWithoutConstructor();
        $attributes = $method->invoke($instance, 'vimeo');

        $this->assertSame('vimeo', $attributes['providerType']['default']);
    }
}
