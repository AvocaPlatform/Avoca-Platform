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
    'users' => array(
        'name' => 'users',
        'ENGINE' => 'InnoDB',
        'fields' => array(
            'id INT 10 unsigned:true auto_increment:true',
            'date_created DATETIME',
            'username VARCHAR 255',
            'password CHAR 32',
            'is_admin TINYINT 1 default:0',
        ),
        'indexes' => array(
            'PK id',
            'UNIQUE username username',
        ),
    ),
    'emails' => array(
        'name' => 'emails',
        'ENGINE' => 'InnoDB',
        'fields' => array(
            'id INT 10 unsigned:true auto_increment:true',
            'date_created DATETIME',
            'from VARCHAR 255',
            'to VARCHAR 255',
            'status TINYINT 1',
            'subject VARCHAR 255',
            'message TEXT',
            'attachments TEXT',
        ),
        'indexes' => array(
            'PK id',
        ),
    ),
);
