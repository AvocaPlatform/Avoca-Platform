<?php
/**
 * Created by AVOCA.IO
 * Website: http://avoca.io
 * User: Jacky
 * Email: hungtran@up5.vn | jacky@youaddon.com
 * Person: tdhungit@gmail.com
 * Skype: tdhungit
 * Git: https://github.com/tdhungit
 */

return [
    //'disabled_search' => true,
    //'disabled_view' => true,
    //'disabled_edit' => true,
    //'disabled_delete' => true,
    'list' => [
        'id' => [
            'type' => 'int',
            'label' => 'ID',
        ],
        'username' => [
            'type' => 'link',
            'controller' => 'users',
            //'label' => 'Username',
            //'nosort' => true,
            'search' => true,
        ],
    ],
    'record' => [
        [
            'id' => [
                'type' => 'readonly'
            ],
            'username' => [],
        ]
    ],
];