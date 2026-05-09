<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Spec Source
    |--------------------------------------------------------------------------
    */

    'default' => env('SPEC_SOURCE', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Sources
    |--------------------------------------------------------------------------
    */

    'sources' => [
        'local' => [
            'source' => 'local',
            'base_path' => env('SPEC_PATH', base_path('../docs/specs')),
        ],

        'remote' => [
            'source' => 'remote',
            'base_path' => env('SPEC_PATH'),
            'params' => env('SPEC_URL_PARAMS', ''),
        ],

        'github' => [
            'source' => 'github',
            'base_path' => env('SPEC_GITHUB_PATH'),
            'repo' => env('SPEC_GITHUB_REPO'),
            'token' => env('SPEC_GITHUB_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    */

    'path_prefix' => '',

    /*
    |--------------------------------------------------------------------------
    | Error Format
    |--------------------------------------------------------------------------
    */

    'error_format' => env('SPECTATOR_ERROR_FORMAT', 'text'),

    /*
    |--------------------------------------------------------------------------
    | Middleware Groups
    |--------------------------------------------------------------------------
    |
    | API routes in this project are defined under routes/web.php with an
    | `api/` URL prefix, so Spectator's middleware must be attached to the
    | `web` group rather than the framework default `api`.
    |
    */

    'middleware_groups' => ['web'],
];
