<?php

return [
    "stubs" => [
        'sample' => [
            'description' => 'This is a sample stub',
            'stub' => 'stubs/sample.stub',
            'values' => [
                'timestamp' => now()->toString()
            ],
            'file_path' => 'samples/',
            'file_extension' => '.txt',
            'filename_case' => 'Studly',
        ],
    ]
];
