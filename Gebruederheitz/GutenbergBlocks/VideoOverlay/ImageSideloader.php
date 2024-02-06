<?php

namespace Gebruederheitz\GutenbergBlocks\VideoOverlay;

/* See https://developer.wordpress.org/reference/functions/media_sideload_image/#more-information */
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

use Gebruederheitz\Wordpress\Rest\Traits\withREST;

class ImageSideloader
{
    use withREST;

    public function __construct()
    {
        $this->initInstanceRestApi();
    }

    /**
     * @param \WP_REST_Request<array<mixed>> $request
     * @return array<string, string>
     */
    public function restSideload(\WP_REST_Request $request): array
    {
        $url = $request->get_param('imageUrl');
        [$mediaId, $mediaUrl] = $this->sideload($url);

        return [
            'mediaId' => $mediaId,
            'mediaUrl' => $mediaUrl,
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    public static function getRestRoutes(): array
    {
        return [];
    }

    /**
     * @return array<array<string, mixed>>
     */
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
                            'description' =>
                                'The image URL to load into the media library',
                            'default' => '',
                            'type' => 'string',
                            'sanitize_callback' => 'esc_url_raw',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function sideload(string $imageUrl): array
    {
        $mediaId = \media_sideload_image($imageUrl, null, null, 'id');
        $mediaUrl = \wp_get_attachment_url($mediaId);

        return [$mediaId, $mediaUrl];
    }
}
