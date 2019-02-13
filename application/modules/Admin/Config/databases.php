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

$db = [
    'users' => [
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
    'emails' => [
        'name' => 'emails',
        'ENGINE' => 'InnoDB',
        'fields' => [
            'id INT 10 unsigned:true auto_increment:true',
            'date_created DATETIME',
            'from VARCHAR 255',
            'to VARCHAR 255',
            'status TINYINT 1',
            'subject VARCHAR 255',
            'message TEXT',
            'attachments TEXT',
        ],
        'indexes' => [
            'PK id',
        ],
    ],
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
];

$custom = [];
if (file_exists(CUSTOMPATH . 'modules/Admin/Config/databases.php')) {
    $custom = include CUSTOMPATH . 'modules/Admin/Config/databases.php';
}

return array_merge($custom, $db);
