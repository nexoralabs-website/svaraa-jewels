<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' =&gt; env('FILESYSTEM_PUBLIC_DISK', 'local') === 's3'
            ? [
                'driver' =&gt; 's3',
                'key' =&gt; env('SUPABASE_ACCESS_KEY_ID'),
                'secret' =&gt; env('SUPABASE_SECRET_ACCESS_KEY'),
                'region' =&gt; env('SUPABASE_REGION', 'us-east-1'),
                'bucket' =&gt; env('SUPABASE_BUCKET'),
                'url' =&gt; env('SUPABASE_URL'),
                'endpoint' =&gt; env('SUPABASE_ENDPOINT'),
                'use_path_style_endpoint' =&gt; env('SUPABASE_USE_PATH_STYLE_ENDPOINT', true),
                'visibility' =&gt; 'public',
                'throw' =&gt; false,
                'report' =&gt; false,
            ]
            : [
                'driver' =&gt; 'local',
                'root' =&gt; storage_path('app/public'),
                'url' =&gt; env('APP_URL').'/storage',
                'visibility' =&gt; 'public',
                'throw' =&gt; false,
                'report' =&gt; false,
            ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'auto'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
