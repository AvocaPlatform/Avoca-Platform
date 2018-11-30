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
        'subject' => [],
        'from' => [],
        'to' => [],
    ],
    'record' => [
        [
            'from' => [],
            'to' => [],
        ],
        [
            'subject' => [],
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
        ]
    ],
];