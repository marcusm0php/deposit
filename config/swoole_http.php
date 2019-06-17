<?php 

return [
    'server' => [
        'host' => env('SWOOLE_HTTP_HOST', '0.0.0.0'),
        'port' => env('SWOOLE_HTTP_PORT', '1215'),
        'options' => [
    //         'pid_file' => env('SWOOLE_HTTP_PID_FILE', base_path('storage/logs/swoole_http.pid')),
    //         'log_file' => env('SWOOLE_HTTP_LOG_FILE', base_path('storage/logs/swoole_http.log')),
    //         'daemonize' => env('SWOOLE_HTTP_DAEMONIZE', 1),
        ],
    ],
];

