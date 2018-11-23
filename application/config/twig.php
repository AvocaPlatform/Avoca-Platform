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

$config['twig'] = [
    'functions' => [
        'avoca_static',
        'get_flash',
        '__',
        'avoca_admin',
        'avoca_manage',
        'avoca_currentUrl',
        'recordLink',
        'fieldLabel',
    ],
    'functions_safe' => [
        'form_input',
        'form_dropdown',
        'form_password',
        'form_multiselect',
        'form_textarea',
        'avoca_form',
        'recordFVal',
        'recordVal',
    ]
];