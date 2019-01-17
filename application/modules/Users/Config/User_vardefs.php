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

return array(
    'module' => 'Users',
    'model' => 'User',
    'table' => 'users',
    'fields' => [
        'id' => [
            'type' => 'ID',
        ],
        'date_created' => [
            'type' => 'DATETIME',
        ],
        'username' => [
            'type' => 'VARCHAR',
            'constraint' => 255,
        ],
        'password' => [
            'type' => 'CHAR',
            'constraint' => 32,
        ],
        'is_admin' => [
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
        ],
    ],
    'indexes' => [
        'primary' => [
            'type' => 'PK',
            'fields' => [
                'id',
            ],
        ],
        'username' => [
            'type' => 'UNIQUE',
            'fields' => [
                'username',
            ]
        ]
    ],
    'relationships' => [
        'user_email' => [
            'field' => 'id',
            'module' => 'emails',
            'rfield' => 'id',
        ]
    ],
);