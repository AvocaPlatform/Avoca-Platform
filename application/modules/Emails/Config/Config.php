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
    'module' => 'Emails',
    'model' => 'Email',
    'database' => [
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
    ]
];