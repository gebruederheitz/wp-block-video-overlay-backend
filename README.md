# Wordpress Block Video Overlay (Backend Module)

see [@gebruederheitz/wp-block-video-overlay](https://www.npmjs.com/package/@gebruederheitz/wp-block-video-overlay)


## Usage

### Registering the block

Given you have already initialized the 
[gebruederheitz/wp-gutenberg-blocks](https://packagist.org/packages/gebruederheitz/wp-gutenberg-blocks)
BlockRegistrar somewhere:

```php
\Gebruederheitz\GutenbergBlocks\BlockRegistrar::getInstance();
```

You will then simply have to instantiate a `VideoOverlayBlock`:

```php
new \Gebruederheitz\GutenbergBlocks\VideoOverlay\VideoOverlayBlock();
```


### Using consent management functionality

Managing user consent of third-party platforms is quite simple with this block
and [`@gebruederheitz/consent-tools`](https://www.npmjs.com/package/@gebruederheitz/consent-tools).
To provide the editor scripts in 
[`@gebruderheitz/wp-block-video-overlay`](https://www.npmjs.com/package/@gebruederheitz/wp-block-video-overlay) 
with a list of possible embed types, use the filter hook:

```php
use Gebruederheitz\GutenbergBlocks\VideoOverlay\VideoOverlayBlock;

add_filter(VideoOverlayBlock::HOOK_EMBED_TYPES, function ($embedTypes) use $services {
    $embedTypes[''] = ['displayName' => 'internal'];

        foreach ($services as $serviceId => $service) {
            $embedTypes[$serviceId] = [
                'displayName' => $service['prettyName'] ?? $serviceId,
            ];
        }

        return $embedTypes;
});
```

#### Changing the default embed provider

You can simply pass the default service's identifier to the constructor:

```php
use Gebruederheitz\GutenbergBlocks\VideoOverlay\VideoOverlayBlock;

new VideoOverlayBlock();   // Default: 'youtube' service (data-ghct-src and data-ghct-type="youtube")
new VideoOverlayBlock(''); // Default: no consent management (src and no data-ghct-src)
new VideoOverlayBlock('vimeo') // Default: 'vimeo' service (data-ghct-src and data-ghct-type="vimeo")
```


### Enabling video captions

Use the filter hook `HOOK_CC_LANG_PREFS` to provide an array of available
languages on your site to allow editors to input a language preset for Youtube
captions:

```php
add_filter(VideoOverlayBlock::HOOK_CC_LANG_PREFS, function ($enabled) {
    /* ... */
    return true;
});
```

Editors may input a string in the Block Editor representing a language or locale
slug (like "en", "en-GB", "en-US", "fr", "de-DE" etc.). The input is sanitized
to only allow alphanumeric characters, dashes and underscores.


### Defining additional attributes

Yes, there's a filter hook for that:

```php
use Gebruederheitz\GutenbergBlocks\VideoOverlay\VideoOverlayBlock;

add_filter(VideoOverlayBlock::HOOK_ATTRIBUTES, function ($attributes) {
    $attributes['myCustomAttribute'] = [
        'type' => 'string',
        'default' => 'attribute default value',
    ];
    
    return $attributes;
})
```

### Changing the block markup

You can override the template used by the block by simply putting a file into
`wp-content/themes/{your-theme}/template-parts/blocks/video-overlay.php`. The 
block's attributes are accessible using `get_query_var('attributeName')`. Take 
look at [the default template](templates/video-overlay.php) as an example.

Alternatively you can filter & override the template partial path and gain even
more control:

```php
add_filter(VideoOverlayBlock::HOOK_TEMPLATE_PARTIAL, function (string $partial, string $type, array $attributes) {
    if ($type === 'overlay') {
        return get_theme_file_path('templates/block/video/video-type-overlay.php');
    }
    
    return $partial;
}, 3);
```
