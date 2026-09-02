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
