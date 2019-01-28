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
    //'disabled_search' => true,
    //'disabled_view' => true,
    //'disabled_edit' => true,
    //'disabled_delete' => true,
    'title' => 'username',
    'list' => [
        'fields' => [
            'id' => [
                'name' => 'id',
                'type' => 'int',
            ],
            'username' => [
                'name' => 'username',
                'type' => 'link',
                'controller' => 'Users',
                //'label' => 'Username',
                //'nosort' => true,
                'search' => true,
                'operator' => 'like', // option
            ],
        ]
    ],
    'record' => [
        'fields' => [
            [
                'id' => [
                    'name' => 'id',
                    'type' => 'disabled'
                ],
                'username' => [
                    'name' => 'username',
                ],
            ],
        ],
//        'buttons' => [
//            'save' => [
//                'label' => 'Save'
//            ],
//            'cancel' => true,
//            'more' => [
//                [
//                    'type' => 'button',
//                    'label' => 'CC',
//                    'color' => 'info',
//                    'icon' => 'fa fa-cogs',
//                    'click' => "alert('123')"
//                ],
//                [
//                    'label' => 'BC'
//                ]
//            ]
//        ],
    ],
//    'js' => [
//        //''
//    ],
//    'css' => [
//        //''
//    ],
];