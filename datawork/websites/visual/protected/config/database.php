<?php

return [
    'connections' => [
        ###############----------------------------###############
        # metric_meta 主
        [
            'db'     => env('DB_MASTER_METRIC_META_DATABASE'),
            'host'   => env('DB_MASTER_METRIC_META_HOST'),
            'port'   => env('DB_MASTER_METRIC_META_PORT'),
            'weight' => 1,
            'user'   => env('DB_MASTER_METRIC_META_USERNAME'),
            'pass'   => env('DB_MASTER_METRIC_META_PASSWORD'),
            'master' => 1,
            'suffix' => '',
        ],
        # metric_meta 从
        [
            'db'     => env('DB_SLAVE_METRIC_META_DATABASE'),
            'host'   => env('DB_SLAVE_METRIC_META_HOST'),
            'port'   => env('DB_SLAVE_METRIC_META_PORT'),
            'weight' => 1,
            'user'   => env('DB_SLAVE_METRIC_META_USERNAME'),
            'pass'   => env('DB_SLAVE_METRIC_META_PASSWORD'),
            'master' => 0,
            'suffix' => '',
        ],
        ###############----------------------------###############

        ###############----------------------------###############
        # metric_real_data 主
        [
            'db'     => env('DB_MASTER_METRIC_REAL_DATA_DATABASE'),
            'host'   => env('DB_MASTER_METRIC_REAL_DATA_HOST'),
            'port'   => env('DB_MASTER_METRIC_REAL_DATA_PORT'),
            'weight' => 1,
            'user'   => env('DB_MASTER_METRIC_REAL_DATA_USERNAME'),
            'pass'   => env('DB_MASTER_METRIC_REAL_DATA_PASSWORD'),
            'master' => 1,
            'suffix' => '',
        ],
        # metric_real_data 从
        [
            'db'     => env('DB_SLAVE_METRIC_REAL_DATA_DATABASE'),
            'host'   => env('DB_SLAVE_METRIC_REAL_DATA_HOST'),
            'port'   => env('DB_SLAVE_METRIC_REAL_DATA_PORT'),
            'weight' => 1,
            'user'   => env('DB_SLAVE_METRIC_REAL_DATA_USERNAME'),
            'pass'   => env('DB_SLAVE_METRIC_REAL_DATA_PASSWORD'),
            'master' => 0,
            'suffix' => '',
        ],
        ###############----------------------------###############

        ###############----------------------------###############
        # dt_db 主
        [
            'db'     => env('DB_MASTER_DT_DB_DATABASE'),
            'host'   => env('DB_MASTER_DT_DB_HOST'),
            'port'   => env('DB_MASTER_DT_DB_PORT'),
            'weight' => 1,
            'user'   => env('DB_MASTER_DT_DB_USERNAME'),
            'pass'   => env('DB_MASTER_DT_DB_PASSWORD'),
            'master' => 1,
            'suffix' => '',
        ],
        # dt_db 从
        [
            'db'     => env('DB_SLAVE_DT_DB_DATABASE'),
            'host'   => env('DB_SLAVE_DT_DB_HOST'),
            'port'   => env('DB_SLAVE_DT_DB_PORT'),
            'weight' => 1,
            'user'   => env('DB_SLAVE_DT_DB_USERNAME'),
            'pass'   => env('DB_SLAVE_DT_DB_PASSWORD'),
            'master' => 0,
            'suffix' => '',
        ],
        ###############----------------------------###############
    ],

    ## other config
];
