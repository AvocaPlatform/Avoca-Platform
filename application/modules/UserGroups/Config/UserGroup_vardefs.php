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
    'module' => 'UserGroups',
    'model' => 'UserGroup',
    'table' => 'user_groups',
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
            'label' => 'Group name',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'parent_id' => [
            'name' => 'parent_id',
            'label' => 'Parent',
            'fname' => 'parent_name',
            'type' => 'relate',
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