<?php
    use Gebruederheitz\GutenbergBlocks\PartialRenderer;

    $videoUrl     = get_query_var('videoUrl');
    $mediaURL     = get_query_var('mediaURL');
    $mediaID      = get_query_var('mediaID');
    $mediaAltText = get_query_var('mediaAltText');
    $providerType = get_query_var('providerType');
    $className    = get_query_var('className') ?? '';
    $type         = get_query_var('type');
    $embedUrl     = get_query_var('videoEmbedUrl');
    $ccLangPref   = get_query_var('ccLangPref');
    $lazyLoadPreviewImage = get_query_var('lazyLoadPreviewImage');

    $classNames = [$className, 'ghwp-video', 'ghwp-video--' . $type];
?>
<div class="<?= implode(' ', $classNames) ?>">
    <?php if ($type === 'overlay'): ?>
        <a
            class="ghwp-video-link"
            <?= empty($providerType) ? 'href' : 'data-ghct-src' ?>="<?= $videoUrl ?>"
            <?php if (!empty($providerType)) echo 'data-ghct-type="'. $providerType . '"'; ?>
        >
            <img
                width="480"
                height="270"
                loading="<?= $lazyLoadPreviewImage ? 'lazy' : 'eager' ?>"
                src="<?= $mediaURL ?>"
                alt="<?= $mediaAltText ?>"
                class="ghwp-video__thumb<?= $mediaID ? " wp-image-$mediaID" : '' ?>"
            />
            <?= PartialRenderer::render(__DIR__ . '/play-icon.php'); ?>
        </a>
    <?php elseif ($type === 'inline'): ?>
        <div class="ghwp-video-image" style='background-image: url("<?= $mediaURL ?>");'>
            <iframe
                <?= empty($providerType) ? 'src' : 'data-ghct-src' ?>="<?= $embedUrl ?>"
                <?php if (!empty($providerType)) echo 'data-ghct-type="'. $providerType . '"'; ?>
                <?php if (!empty($ccLangPref)) echo 'cc_load_policy=1 cc_lang_pref="'. $ccLangPref . '"'; ?>
            ></iframe>
            <?= PartialRenderer::render(__DIR__ . '/play-icon.php'); ?>
        </div>
    <?php endif; ?>
</div>
