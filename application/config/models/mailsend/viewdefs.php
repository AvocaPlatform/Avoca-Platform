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
    'disabled_edit' => true,
    'disabled_delete' => true,
    'title' => 'subject',
    'list' => [
        'fields' => [
            'subject' => [],
            'from' => [],
            'to' => [],
        ]
    ],
    'record' => [
        'fields' => [
            [
                'from' => true,
                'to' => true,
            ],
            [
                'subject' => true,
            ],
            [
                'message' => [
                    'type' => 'textarea'
                ]
            ],
            [
                'attachments' => [
                    'type' => 'files'
                ]
            ],
        ],
        'buttons' => [
            'save' => [
                'label' => 'Send'
            ],
            'cancel' => true
        ],
    ],
];