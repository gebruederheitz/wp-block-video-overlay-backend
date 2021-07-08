<?php

    $videoUrl     = get_query_var('videoUrl');
    $mediaURL     = get_query_var('mediaURL');
    $mediaID      = get_query_var('mediaID');
    $mediaAltText = get_query_var('mediaAltText');
    $providerType = get_query_var('providerType');

?>
<div class="ghwp-video">
    <a
        class="ghwp-video-link"
        data-ghwp-src="<?= $videoUrl ?>"
        data-ghwp-type="<?= $providerType ?>"
    >
        <img
            width="480"
            height="270"
            src="<?= $mediaURL ?>"
            alt="<?= $mediaAltText ?>"
            class="ghwp-video__thumb<?= $mediaID ? " wp-image-$mediaID" : '' ?>"
        />
        <svg
            class="icon-play-circle"
            xmlns="http://www.w3.org/2000/svg"
            width="100"
            height="100"
            viewBox="0 0 100 100"
        >
            <path
                d="M50,100a50,50,0,1,1,50-50A50.0575,50.0575,0,0,1,50,100ZM50,6.7754A43.224,43.224,0,1,0,93.2235,50,43.2732,43.2732,0,0,0,50,6.7754Z"/>
            <polygon
                points="39.724 67.356 39.72 32.644 69.585 50 39.724 67.356"/>
        </svg>
    </a>
</div>
