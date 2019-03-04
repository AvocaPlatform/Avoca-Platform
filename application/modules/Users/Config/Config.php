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
    'module' => 'Users',
    'model' => 'User',
    'database' => [
        'name' => 'users',
        'ENGINE' => 'InnoDB',
        'fields' => [
            'id INT 10 unsigned:true auto_increment:true',
            'date_created DATETIME',
            'username VARCHAR 255',
            'password CHAR 32',
            'is_admin TINYINT 1 default:0',
        ],
        'indexes' => [
            'PK id',
            'UNIQUE username username',
        ],
    ],
];