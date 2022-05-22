<?php

return [
    "stubs" => [
        'sample' => [
            'stub' => 'stubs/sample.stub',
            'defaults' => [
                'timestamp' => now()->toString()
            ],
            'path' => 'samples/',
            'extension' => '.txt',
            'description' => 'This is a sample stub',
        ],
    ]
];
