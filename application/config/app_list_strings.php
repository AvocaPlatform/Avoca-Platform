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

$app_list_strings = [
    'status_list' => [
        'Yes' => 'Yes',
        'No' => 'No',
    ]
];

if (file_exists(CUSTOMPATH . 'config/app_list_strings.php')) {
    $custom_list_strings = include CUSTOMPATH . 'config/app_list_strings.php';
    $app_list_strings = array_merge($app_list_strings, $custom_list_strings);
}

$config['app_list_strings'] = $app_list_strings;