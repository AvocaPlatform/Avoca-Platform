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
            'subject' => [
                'name' => 'subject',
            ],
            'from' => [
                'name' => 'from',
            ],
            'to' => [
                'name' => 'to',
            ],
        ]
    ],
    'record' => [
        'fields' => [
            [
                'from' => [
                    'name' => 'from',
                ],
                'to' => [
                    'name' => 'to',
                ],
            ],
            [
                'subject' => [
                    'name' => 'subject',
                ],
            ],
            [
                'message' => [
                    'type' => 'textarea',
                    'name' => 'message',
                ]
            ],
            [
                'attachments' => [
                    'type' => 'files',
                    'name' => 'attachments',
                ]
            ],
        ],
        'buttons' => [
            'save' => [
                'label' => 'Send'
            ],
            'cancel' => true,
            'more' => [
                [
                    'type' => 'button',
                    'label' => 'CC',
                    'color' => 'info',
                    'icon' => 'fa fa-cogs',
                    'click' => "alert('123')"
                ],
                [
                    'type' => 'button',
                    'label' => 'BC',
                ],
                [
                    'type' => 'button',
                    'label' => 'Popup',
                    'color' => 'warning',
                    'click' => 'sendMail()',
                ]
            ]
        ],
    ],
    'js' => [
        //''
    ],
    'css' => [
        //''
    ],
];