<?php
return [
    'default_profile' => 'default',
    'profiles' => [
        'default' => [
            /*
            |--------------------------------------------------------------------------
            | Array of file types that accept to upload
            | - array: array of file type (only in config.file_types.uploadable)
            | - true: config.file_types.uploadable
            */
            'file_types' => true,

            /*
            |--------------------------------------------------------------------------
            | File size, in bytes
            */
            'size' => 3145728, //3MB

            /*
            |--------------------------------------------------------------------------
            | Name of temporary uploaded file, use after move to temporary folder.
            | Use only when handler is TemporaryUploadHandler's instance
            |
            | - null: original name - beware
            | - 'hash': hash file name by it's content
            | - 'random': random 16 charts
            */
            'temporary_name' => 'random',

            /*
            |--------------------------------------------------------------------------
            | Absolute store path
            */
            'store_path' => storage_path('app/upload'),
        ]
    ],
    'upload_temp_path' => storage_path('app/upload_temp'),

    /*
    |--------------------------------------------------------------------------
    | Live time of uploaded temporary files
    */
    'temporary_live_time' => 86400 //1 day
];
