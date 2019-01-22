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
    'module' => 'Emails',
    'model' => 'Email',
    'table' => 'emails',
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'id',
        ],
        'date_created' => [
            'name' => 'date_created',
            'type' => 'datetime',
        ],
        'from' => [
            'name' => 'from',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'to' => [
            'name' => 'to',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'status' => [
            'name' => 'status',
            'type' => 'tinyint',
            'constraint' => 1,
            'default' => 0,
        ],
        'subject' => [
            'name' => 'subject',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'message' => [
            'name' => 'message',
            'type' => 'text',
        ],
        'attachments' => [
            'name' => 'attachments',
            'type' => 'text',
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
);