<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay;

/* See https://developer.wordpress.org/reference/functions/media_sideload_image/#more-information */
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

use Gebruederheitz\Traits\withRest;

class ImageSideloader
{
    use withREST;

    public function __construct()
    {
        $this->initInstanceRestApi();
    }

    public function restSideload(\WP_REST_Request $request): array
    {
        $url = $request->get_param('imageUrl');
        [$mediaId, $mediaUrl] = $this->sideload($url);

        return [
            'mediaId' => $mediaId,
            'mediaUrl' => $mediaUrl,
        ];
    }

    public static function getRestRoutes(): array
    {
        return [];
    }

    public function getInstanceRestRoutes(): array
    {
        return [
            [
                'name' => 'Sideload an image by providing its URL',
                'route' => '/sideload',
                'config' => [
                    'methods' => 'POST',
                    'callback' => [$this, 'restSideload'],
                    'permission_callback' => function () {
                        return current_user_can('edit_posts');
                    },
                    'args' => [
                        'imageUrl' => [
                            'description' => 'The image URL to load into the media library',
                            'default' => '',
                            'type' => 'string',
                            'sanitize_callback' => 'esc_url_raw',
                        ]
                    ]
                ]
            ],
        ];
    }

    private function sideload(string $imageUrl): array
    {
        $mediaId = \media_sideload_image($imageUrl, null, null, 'id');
        $mediaUrl = \wp_get_attachment_url($mediaId);

        return [
            $mediaId,
            $mediaUrl,
        ];
    }
}
