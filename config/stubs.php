<?php

return [
    "options" => [
        "delimeters" => ["{{", "}}"],
    ],
    "builds" => [
        'sample' => [
            'description' => 'This is a sample stub',
            'values' => [
                'timestamp' => now()->toString()
            ],
            'stubs' => [
                'stubs/sample1.stub' => [
                    'filename' => "@FILENAMEDemo",
                    'filename_case' => 'Studly',
                    'file_path' => 'samples/',
                    'file_extension' => '.txt',
                ],
                'stubs/sample2.stub' => [
                    'filename' => "samples/demo-@FILENAME.txt",
                    'filename_case' => 'Kebab',
                ],
            ],
        ],
    ]
];
