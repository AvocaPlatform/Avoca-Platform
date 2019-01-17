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
                'label' => 'Subject',
            ],
            'from' => [
                'name' => 'from',
                'label' => 'From',
            ],
            'to' => [
                'name' => 'name',
                'label' => 'To',
            ],
        ]
    ],
    'record' => [
        'fields' => [
            [
                'from' => [
                    'name' => 'from',
                    'label' => 'From',
                ],
                'to' => [
                    'name' => 'to',
                    'label' => 'To',
                ],
            ],
            [
                'subject' => [
                    'name' => 'subject',
                    'label' => 'Subject',
                ],
            ],
            [
                'message' => [
                    'type' => 'textarea',
                    'name' => 'message',
                    'label' => 'Message',
                ]
            ],
            [
                'attachments' => [
                    'type' => 'files',
                    'name' => 'attachments',
                    'label' => 'Attachments',
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