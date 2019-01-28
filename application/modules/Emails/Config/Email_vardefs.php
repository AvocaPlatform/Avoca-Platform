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
            'label' => 'Id',
            'type' => 'id',
        ],
        'date_created' => [
            'name' => 'date_created',
            'label' => 'Date created',
            'type' => 'datetime',
        ],
        'from' => [
            'name' => 'from',
            'label' => 'From',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'to' => [
            'name' => 'to',
            'label' => 'To',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'status' => [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'tinyint',
            'constraint' => 1,
            'default' => 0,
        ],
        'subject' => [
            'name' => 'subject',
            'label' => 'Subject',
            'type' => 'varchar',
            'constraint' => 255,
        ],
        'message' => [
            'name' => 'message',
            'label' => 'Message',
            'type' => 'text',
        ],
        'attachments' => [
            'name' => 'attachments',
            'label' => 'Attachments',
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