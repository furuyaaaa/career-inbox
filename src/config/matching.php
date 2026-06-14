<?php

return [
    'driver' => env('MATCHING_DRIVER', 'php'),
    'python_binary' => env('MATCHING_PYTHON_BINARY', 'python3'),
    'python_script' => env('MATCHING_PYTHON_SCRIPT', base_path('python/matching_service.py')),
];
