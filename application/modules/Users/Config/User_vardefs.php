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
            'name' => 'id',
            'type' => 'id',
        ],
        'date_created' => [
            'name' => 'date_created',
            'type' => 'datetime',
        ],
        'username' => [
            'name' => 'username',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'password' => [
            'name' => 'password',
            'type' => 'char',
            'constraint' => 32,
        ],
        'is_admin' => [
            'name' => 'is_admin',
            'type' => 'tinyint',
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