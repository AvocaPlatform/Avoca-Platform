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
    'databases' => [
        'user_groups' => [
            'name' => 'user_groups',
            'ENGINE' => 'InnoDB',
            'fields' => [
                'id INT 10 unsigned:true auto_increment:true',
                'date_created DATETIME',
                'name VARCHAR 255',
                'description TEXT',
                'parent_id INT 10',
            ],
            'indexes' => [
                'PK id',
            ],
        ],
    ],
];