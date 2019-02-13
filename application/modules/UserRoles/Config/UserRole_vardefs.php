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
    'module' => 'UserRoles',
    'model' => 'UserRole',
    'table' => 'user_roles',
    'fields' => [
        'id' => [
            'name' => 'id',
            'label' => 'Id',
            'type' => 'id',
        ],
        'date_created' => [
            'name' => 'date_created',
            'label' => 'Date created',
            'type' => 'datetime',
        ],
        'name' => [
            'name' => 'name',
            'label' => 'Role name',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'description' => [
            'name' => 'description',
            'label' => 'Description',
            'type' => 'varchar',
            'constraint' => 255,
        ],
    ],
    'indexes' => [
        'primary' => [
            'type' => 'PK',
            'fields' => [
                'id',
            ],
        ],
    ],
];