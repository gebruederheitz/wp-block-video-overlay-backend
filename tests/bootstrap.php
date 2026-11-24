<?php

/**
 * Minimal PHPUnit bootstrap for wp-block-video-overlay-backend.
 *
 * This intentionally does NOT pull in a WordPress runtime (the
 * johnpbloch/wordpress-core dev dependency is only used to feed phpstan
 * with WP stubs). Instead we define the handful of WordPress functions
 * that the code under test actually calls, as minimal
 * passthroughs/recorders, so the classes can be exercised in isolation.
 *
 * vendor/ already contains the real gebruederheitz/wp-easy-customizer,
 * wp-gutenberg-blocks and wp-simple-rest classes, so we only stub bare
 * WordPress core functions here - not those packages' own APIs.
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        // No-op recorder: the tests only care about object construction /
        // pure logic, not about WordPress actually dispatching the hook.
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        // No-op recorder, see add_action() above.
        return true;
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($hook, $value, ...$args)
    {
        // No filters are registered in the test environment, so behave
        // like WordPress does with no callbacks attached: pass the value
        // through unchanged.
        return $value;
    }
}

/**
 * Theme mod fixture storage for get_theme_mod(), settable by tests via
 * gh_test_set_theme_mod() to exercise different customizer values without
 * needing a real WP_Customize_Manager.
 *
 * @var array<string, mixed>
 */
$GLOBALS['__ghTestThemeMods'] = [];

if (!function_exists('gh_test_set_theme_mod')) {
    function gh_test_set_theme_mod(string $name, $value): void
    {
        $GLOBALS['__ghTestThemeMods'][$name] = $value;
    }
}

if (!function_exists('gh_test_reset_theme_mods')) {
    function gh_test_reset_theme_mods(): void
    {
        $GLOBALS['__ghTestThemeMods'] = [];
    }
}

if (!function_exists('get_theme_mod')) {
    function get_theme_mod($name, $default = false)
    {
        return $GLOBALS['__ghTestThemeMods'][$name] ?? $default;
    }
}

if (!function_exists('sanitize_locale_name')) {
    function sanitize_locale_name($localeName)
    {
        // Same as WordPress core, minus the filter.
        return preg_replace('/[^A-Za-z0-9_-]/', '', $localeName);
    }
}

/**
 * Query var storage, like WP_Query's: PartialRenderer hands block attributes
 * to templates through set_query_var() / get_query_var().
 *
 * @var array<string, mixed>
 */
$GLOBALS['__ghTestQueryVars'] = [];

if (!function_exists('set_query_var')) {
    function set_query_var($key, $value)
    {
        $GLOBALS['__ghTestQueryVars'][$key] = $value;
    }
}

if (!function_exists('get_query_var')) {
    function get_query_var($key, $default = '')
    {
        return array_key_exists($key, $GLOBALS['__ghTestQueryVars'])
            ? $GLOBALS['__ghTestQueryVars'][$key]
            : $default;
    }
}

/*
 * Escaping, modelled on wp-includes/formatting.php but without filters,
 * options or entity normalisation. The template tests check that every value
 * goes through the right escaper; they do not test WordPress's escaping.
 */
if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url, $protocols = null, $_context = 'display')
    {
        $url = (string) $url;
        if ($url === '') {
            return '';
        }

        $url = str_replace(' ', '%20', ltrim($url));
        $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\x80-\xff]|i', '', $url);
        if ($url === '') {
            return '';
        }
        $url = str_ireplace(['%0d', '%0a'], '', $url);

        // No scheme: core prepends http:// unless the URL is relative.
        if (!str_contains($url, ':') && !in_array($url[0], ['/', '#', '?'], true)) {
            $url = 'http://' . $url;
        }

        // Core's wp_kses_bad_protocol(): an unknown scheme empties the URL.
        if (
            preg_match('/^([^\/?#]*?):/', $url, $matches) &&
            !in_array(strtolower($matches[1]), ['http', 'https', 'mailto'], true)
        ) {
            return '';
        }

        // Encode ampersands and single quotes only for display, not for 'db'.
        if ($_context !== 'display') {
            return $url;
        }
        $url = preg_replace('/&(?!#?[a-z0-9]+;)/i', '&#038;', $url);

        return str_replace("'", '&#039;', $url);
    }
}

if (!function_exists('sanitize_url')) {
    function sanitize_url($url, $protocols = null)
    {
        return esc_url($url, $protocols, 'db');
    }
}

if (!function_exists('sanitize_html_class')) {
    function sanitize_html_class($classname, $fallback = '')
    {
        $sanitized = preg_replace('|%[a-fA-F0-9][a-fA-F0-9]|', '', (string) $classname);
        $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $sanitized);

        if ($sanitized === '' && $fallback) {
            return sanitize_html_class($fallback);
        }

        return $sanitized;
    }
}

if (!function_exists('wp_get_attachment_image')) {
    function wp_get_attachment_image($attachmentId, $size = 'thumbnail', $icon = false, $attr = '')
    {
        // No attachments exist, so templates take their fallback path.
        return '';
    }
}

if (!function_exists('wp_reset_postdata')) {
    function wp_reset_postdata()
    {
    }
}

/**
 * Records every load_template() call, so tests can inspect the data a block
 * hands to its template via gh_test_loaded_templates(), and renders the file
 * if it exists.
 *
 * @var list<array{file: string, args: array<string, mixed>}>
 */
$GLOBALS['__ghTestLoadedTemplates'] = [];

if (!function_exists('load_template')) {
    function load_template($file, $loadOnce = true, $args = [])
    {
        $GLOBALS['__ghTestLoadedTemplates'][] = ['file' => $file, 'args' => $args];

        // Render real templates; tests may pass a path that doesn't exist to
        // only inspect the data.
        if (is_file($file)) {
            (static function () use ($file, $args) {
                require $file;
            })();
        }
    }
}

if (!function_exists('gh_test_loaded_templates')) {
    /**
     * @return list<array{file: string, args: array<string, mixed>}>
     */
    function gh_test_loaded_templates(): array
    {
        return $GLOBALS['__ghTestLoadedTemplates'];
    }
}

if (!function_exists('gh_test_reset_loaded_templates')) {
    function gh_test_reset_loaded_templates(): void
    {
        $GLOBALS['__ghTestLoadedTemplates'] = [];
    }
}
