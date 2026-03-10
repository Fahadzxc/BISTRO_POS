<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'auth'          => \App\Filters\AuthFilter::class,
        'role'          => \App\Filters\RoleFilter::class,
    ];

    public array $required = [
        'before' => [
            'forcehttps',
            'pagecache',
        ],
        'after' => [
            'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    public array $globals = [
        'before' => [
            'csrf' => ['except' => [
                'pos/add-to-cart',
                'pos/update-cart',
                'pos/remove-from-cart',
                'pos/get-cart',
                'pos/checkout',
                'ktv-rooms/get-rooms',
                'ktv-rooms/start',
                'ktv-rooms/pause',
                'ktv-rooms/resume',
                'ktv-rooms/end',
                'ktv-rooms/set-available',
                'inventory/list',
                'inventory/adjust',
                'inventory/update-min-stock',
                'inventory/logs',
                'categories/store',
                'categories/update',
                'products/store',
                'products/update',
            ]],
        ],
        'after' => [],
    ];

    public array $methods = [];

    public array $filters = [];
}
