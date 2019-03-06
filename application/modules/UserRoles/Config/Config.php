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
    'databases' => [
        'user_roles' => [
            'name' => 'user_roles',
            'ENGINE' => 'InnoDB',
            'fields' => [
                'id INT 10 unsigned:true auto_increment:true',
                'date_created DATETIME',
                'name VARCHAR 255',
                'description TEXT',
            ],
            'indexes' => [
                'PK id',
            ],
        ],
    ],
];