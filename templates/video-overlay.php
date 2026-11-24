<?php
    use Gebruederheitz\GutenbergBlocks\PartialRenderer;

    // Values taken directly from attributes
    $videoUrl                 = get_query_var('videoUrl');
    $mediaURL                 = get_query_var('mediaURL');
    $mediaID                  = (int) get_query_var('mediaID');
    $mediaAltText             = get_query_var('mediaAltText');
    $type                     = get_query_var('type');
    $lazyLoadPreviewImage     = get_query_var('lazyLoadPreviewImage');

    // Derived values
    $classNames               = get_query_var('classNames') ?? [];
    $srcAttributeName         = get_query_var('sourceAttributeName');
    $consentProviderAttribute = get_query_var('consentProviderAttribute') ?? '';
    $playIconPath             = get_query_var('playIconPath') ?? __DIR__.'/play-icon.php';
    $setWidthAndHeight        = get_query_var('setWidthAndHeight') ?? false;

    if ($type === 'overlay') {
        $image = null;
        $imageClassName = 'ghwp-video__thumb';

        if (!empty($mediaID)) {
            $imageClassName .= ' wp-image-'. $mediaID;

            $image = wp_get_attachment_image($mediaID, 'full', false, [
                'class' => $imageClassName,
                'alt' => $mediaAltText,
                'width' => '480',
                'height' => '270',
                'loading' => $lazyLoadPreviewImage ? 'lazy' : 'eager',
            ]);
        }

        $widthAndHeight = $setWidthAndHeight === false ? '' : 'width="480" height="270"';
    }
?>
<div class="<?= esc_attr(implode(' ', $classNames)) ?>">
    <?php if ($type === 'overlay'): ?>
        <a
            class="ghwp-video-link"
            <?= $srcAttributeName ?>="<?= esc_url($videoUrl) ?>"
            <?= $consentProviderAttribute ?>
        >
            <?php
                if ($image) {
                    echo $image;
                } elseif (!empty($mediaURL)) {
                    ?>
                        <img
                            width="480"
                            height="270"
                            loading="<?= $lazyLoadPreviewImage ? 'lazy' : 'eager' ?>"
                            src="<?= esc_url($mediaURL) ?>"
                            alt="<?= esc_attr($mediaAltText) ?>"
                            class="<?= esc_attr($imageClassName) ?>"
                            <?= $widthAndHeight ?>
                        />
                    <?php
                }
            ?>
            <?php PartialRenderer::renderInclude($playIconPath); ?>
        </a>
    <?php elseif ($type === 'inline'): ?>
        <div class="ghwp-video-image" style='background-image: url("<?= esc_url($mediaURL) ?>");'>
            <iframe
                <?= $srcAttributeName ?>="<?= esc_url($videoUrl) ?>"
                <?= $consentProviderAttribute ?>
            ></iframe>
            <?php PartialRenderer::renderInclude($playIconPath); ?>
        </div>
    <?php endif; ?>
</div>
