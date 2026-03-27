<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Asset Containers
    |--------------------------------------------------------------------------
    |
    | Asset containers define where your assets are stored and how they can be
    | managed. Each container can have its own disk, permissions, and settings.
    | Similar to Statamic's container system.
    |
    */

    'containers' => [
        'assets' => [
            'disk' => 'public',
            'title' => 'Assets',
            'allow_uploads' => true,
            'allow_downloading' => true,
            'allow_renaming' => true,
            'allow_moving' => true,
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'pdf'],
        ],
        'images' => [
            'disk' => 'public',
            'title' => 'Images',
            'allow_uploads' => true,
            'max_file_size' => 5 * 1024 * 1024, // 5MB
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        ],
        'videos' => [
            'disk' => 'public',
            'title' => 'Videos',
            'allow_uploads' => true,
            'max_file_size' => 100 * 1024 * 1024, // 100MB
            'allowed_extensions' => ['mp4', 'mov', 'avi', 'webm', 'mkv'],
        ],
        'documents' => [
            'disk' => 'public',
            'title' => 'Documents',
            'allow_uploads' => true,
            'max_file_size' => 20 * 1024 * 1024, // 20MB
            'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Glide Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for League\Glide image manipulation server.
    | Glide generates transformed images on-the-fly and caches them.
    |
    */

    'glide' => [
        // Where to cache transformed images
        'cache_disk' => 'local',
        'cache_path' => 'glide/cache',

        // Source files disk
        'source_disk' => 'public',

        // Cache settings
        'cache_lifetime' => 60 * 60 * 24 * 30, // 30 days in seconds
        'cache_cleanup_enabled' => true,
        'cache_cleanup_older_than' => 60 * 60 * 24 * 90, // Delete cached files older than 90 days

        // Watermark configuration
        'watermark' => [
            'enabled' => false,
            'path' => 'watermark.png',
            'width' => 100,
            'height' => 100,
            'fit' => 'contain',
            'position' => 'bottom-right',
            'offset' => 10,
        ],

        // Maximum dimensions (security)
        'max_width' => 5000,
        'max_height' => 5000,

        // Image quality defaults
        'default_quality' => 90,
        'default_format' => 'auto', // auto, jpg, png, gif, webp

        // Allowed parameters (security)
        'allowed_params' => [
            'w', 'h', 'fit', 'crop', 'q', 'fm', 'blur', 'pixel', 'filt', 'mark', 'markw',
            'markh', 'markpad', 'markpos', 'markalpha', 'bg', 'border', 'sharp', 'flip',
            'or', 'dpr', 'p',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Glide Presets
    |--------------------------------------------------------------------------
    |
    | Define preset transformations that can be applied by name.
    | These are similar to Statamic's Glide presets.
    |
    | Usage: asset('image.jpg', 'thumbnail')
    |        asset('image.jpg', ['preset' => 'large', 'q' => 100])
    |
    */

    'presets' => [
        // Thumbnails
        'xs' => [
            'w' => 150,
            'h' => 150,
            'fit' => 'crop',
            'q' => 80,
        ],
        'sm' => [
            'w' => 300,
            'h' => 300,
            'fit' => 'contain',
            'q' => 85,
        ],
        'md' => [
            'w' => 600,
            'h' => 600,
            'fit' => 'contain',
            'q' => 90,
        ],
        'lg' => [
            'w' => 1200,
            'h' => 1200,
            'fit' => 'contain',
            'q' => 90,
        ],
        'xl' => [
            'w' => 2000,
            'h' => 2000,
            'fit' => 'contain',
            'q' => 85,
        ],

        // Specific aspect ratios
        'square' => [
            'w' => 800,
            'h' => 800,
            'fit' => 'crop',
            'crop' => 'focal', // Use focus point if available
            'q' => 90,
        ],
        'landscape' => [
            'w' => 1200,
            'h' => 800,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 90,
        ],
        'portrait' => [
            'w' => 800,
            'h' => 1200,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 90,
        ],
        'widescreen' => [
            'w' => 1920,
            'h' => 1080,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 90,
        ],

        // Product-specific presets
        'product_card' => [
            'w' => 400,
            'h' => 400,
            'fit' => 'contain',
            'bg' => 'FFFFFF',
            'q' => 90,
        ],
        'product_gallery' => [
            'w' => 800,
            'h' => 800,
            'fit' => 'contain',
            'bg' => 'FFFFFF',
            'q' => 95,
        ],
        'product_zoom' => [
            'w' => 2000,
            'h' => 2000,
            'fit' => 'contain',
            'bg' => 'FFFFFF',
            'q' => 90,
        ],

        // WebP variants (modern browsers)
        'webp_thumbnail' => [
            'w' => 150,
            'h' => 150,
            'fit' => 'crop',
            'fm' => 'webp',
            'q' => 80,
        ],
        'webp_large' => [
            'w' => 1200,
            'h' => 1200,
            'fit' => 'contain',
            'fm' => 'webp',
            'q' => 85,
        ],

        // Social media optimized
        'og_image' => [
            'w' => 1200,
            'h' => 630,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 90,
        ],
        'twitter_card' => [
            'w' => 1200,
            'h' => 675,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 90,
        ],
        'instagram_square' => [
            'w' => 1080,
            'h' => 1080,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 90,
        ],

        // Avatar presets
        'avatar_small' => [
            'w' => 50,
            'h' => 50,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 80,
        ],
        'avatar_medium' => [
            'w' => 100,
            'h' => 100,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 85,
        ],
        'avatar_large' => [
            'w' => 200,
            'h' => 200,
            'fit' => 'crop',
            'crop' => 'focal',
            'q' => 90,
        ],

        // Effects
        'blur' => [
            'blur' => 10,
        ],
        'pixelate' => [
            'pixel' => 8,
        ],
        'grayscale' => [
            'filt' => 'greyscale',
        ],
        'sepia' => [
            'filt' => 'sepia',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Responsive Images
    |--------------------------------------------------------------------------
    |
    | Generate multiple sizes for responsive images (srcset).
    | Define width breakpoints for automatic responsive image generation.
    |
    */

    'responsive' => [
        'enabled' => true,
        'breakpoints' => [320, 640, 768, 1024, 1366, 1600, 1920],
        'default_quality' => 85,
        'formats' => ['jpg', 'webp'], // Generate both JPEG and WebP
    ],

    /*
    |--------------------------------------------------------------------------
    | File Type Configuration
    |--------------------------------------------------------------------------
    |
    | MIME type categories and their settings.
    |
    */

    'file_types' => [
        'image' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp', 'image/x-icon'],
            'icon' => 'photo',
        ],
        'video' => [
            'extensions' => ['mp4', 'mov', 'avi', 'webm', 'mkv', 'flv', 'wmv'],
            'mime_types' => ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm', 'video/x-matroska'],
            'icon' => 'video',
        ],
        'audio' => [
            'extensions' => ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'],
            'mime_types' => ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/flac', 'audio/mp4'],
            'icon' => 'music',
        ],
        'document' => [
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
            'mime_types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'icon' => 'document',
        ],
        'archive' => [
            'extensions' => ['zip', 'rar', 'tar', 'gz', '7z'],
            'mime_types' => ['application/zip', 'application/x-rar-compressed', 'application/x-tar', 'application/gzip'],
            'icon' => 'archive',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Validation
    |--------------------------------------------------------------------------
    |
    */

    'validation' => [
        // Check file hash to prevent duplicates
        'check_duplicates' => true,

        // Scan for viruses (requires ClamAV)
        'virus_scan' => env('MEDIA_VIRUS_SCAN', false),

        // Validate image dimensions
        'min_width' => null,
        'min_height' => null,
        'max_width' => 10000,
        'max_height' => 10000,

        // Blocked extensions (security)
        'blocked_extensions' => ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'php'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Optimization
    |--------------------------------------------------------------------------
    |
    | Automatically optimize images on upload.
    |
    */

    'optimization' => [
        'enabled' => true,

        // Image optimization tools
        'tools' => [
            'jpegoptim' => [
                'enabled' => true,
                'path' => env('JPEGOPTIM_PATH', '/usr/bin/jpegoptim'),
                'options' => ['--strip-all', '--all-progressive'],
            ],
            'optipng' => [
                'enabled' => true,
                'path' => env('OPTIPNG_PATH', '/usr/bin/optipng'),
                'options' => ['-o2'],
            ],
            'pngquant' => [
                'enabled' => true,
                'path' => env('PNGQUANT_PATH', '/usr/bin/pngquant'),
                'options' => ['--force', '--quality=85-95'],
            ],
            'svgo' => [
                'enabled' => true,
                'path' => env('SVGO_PATH', '/usr/bin/svgo'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | Serve assets through a CDN.
    |
    */

    'cdn' => [
        'enabled' => env('MEDIA_CDN_ENABLED', false),
        'url' => env('MEDIA_CDN_URL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Metadata Fields
    |--------------------------------------------------------------------------
    |
    | Default metadata fields for all assets (Statamic-style).
    |
    */

    'meta_fields' => [
        'alt' => null,
        'title' => null,
        'caption' => null,
        'description' => null,
        'copyright' => null,
        'credit' => null,
    ],

];
