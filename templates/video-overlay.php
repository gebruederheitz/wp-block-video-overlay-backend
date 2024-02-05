<?php
    use Gebruederheitz\GutenbergBlocks\PartialRenderer;

    // Values taken directly from attributes
    $videoUrl                 = get_query_var('videoUrl');
    $mediaURL                 = get_query_var('mediaURL');
    $mediaID                  = get_query_var('mediaID');
    $mediaAltText             = get_query_var('mediaAltText');
    $type                     = get_query_var('type');
    $lazyLoadPreviewImage     = get_query_var('lazyLoadPreviewImage');

    // Derived values
    $classNames               = get_query_var('classNames') ?? [];
    $srcAttributeName         = get_query_var('sourceAttributeName');
    $consentProviderAttribute = get_query_var('consentProviderAttribute') ?? '';
    $playIconPath             = get_query_var('playIconPath') ?? __DIR__.'/play-icon.php';
    $setWidthAndHeight        = get_query_Var('setWidthAndHeight') ?? false;

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
<div class="<?= implode(' ', $classNames) ?>">
    <?php if ($type === 'overlay'): ?>
        <a
            class="ghwp-video-link"
            <?= $srcAttributeName ?>="<?= $videoUrl ?>"
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
                            src="<?= $mediaURL ?>"
                            alt="<?= $mediaAltText ?>"
                            class="<?= $imageClassName ?>"
                            <?= $widthAndHeight ?>
                        />
                    <?php
                }
            ?>
            <?php PartialRenderer::renderInclude($playIconPath); ?>
        </a>
    <?php elseif ($type === 'inline'): ?>
        <div class="ghwp-video-image" style='background-image: url("<?= $mediaURL ?>");'>
            <iframe
                <?= $srcAttributeName ?>="<?= $videoUrl ?>"
                <?= $consentProviderAttribute ?>
            ></iframe>
            <?php PartialRenderer::renderInclude($playIconPath); ?>
        </div>
    <?php endif; ?>
</div>
