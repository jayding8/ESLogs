<?php
/**
 * Created by PhpStorm.
 * User: jayding
 * Date: 2019/7/3
 * Time: 10:53
 */
return [
    'connections' => [
        'scheme' => env('ES_SCHEME', 'http'),
        'host' => env('ES_HOST', 'localhost'),
        'port' => env('ES_PORT', '9200'),
    ],

    'index' => [
        'logs' => [
            'index' => 'logs',
            'type' => 'logs',
            'mapping' => [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 5,
                        'number_of_replicas' => 1
                    ]
                ],
                'mappings' => [
                    'content' => [
                        'properties' => [
                            'sys_bundle' => [
                                'type' => 'keyword'
                            ],
                            'app_bundle' => [
                                'type' => 'keyword'
                            ],
                            'module_bundle' => [
                                'type' => 'keyword'
                            ],
                            'user_id' => [
                                'type' => 'keyword'
                            ],
                            'user_name' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                                'search_analyzer' => 'ik_smart'
                            ],
                            'op' => [
                                'type' => 'keyword',
                            ],
                            'op_name' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                                'search_analyzer' => 'ik_smart'
                            ],
                            'create_time' => [
                                'type' => 'keyword'
                            ],
                            'timing' => [
                                'type' => 'keyword'
                            ],
                            'ip' => [
                                'type' => 'keyword'
                            ],
                            'analysis' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                                'search_analyzer' => 'ik_smart'
                            ],
                        ]
                    ]
                ]
            ]
        ],
    ],
];