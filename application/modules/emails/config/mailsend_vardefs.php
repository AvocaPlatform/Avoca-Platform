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
    'module' => 'emails',
    'model' => 'mailsend',
    'table' => 'emails',
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'ID',
        ],
        'date_created' => [
            'name' => 'date_created',
            'type' => 'DATETIME',
        ],
        'from' => [
            'type' => 'VARCHAR',
            'constraint' => 255,
        ],
        'to' => [
            'type' => 'VARCHAR',
            'constraint' => 255,
        ],
        'status' => [
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
        ],
        'subject' => [
            'type' => 'VARCHAR',
            'constraint' => 255,
        ],
        'message' => [
            'type' => 'VARCHAR',
        ],
        'attachments' => [
            'type' => 'VARCHAR',
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