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

$module = [
    'Users' => [
        'module' => 'Users',
        'model' => 'User',
    ],
    'Emails' => [
        'module' => 'Emails',
        'model' => 'Email',
    ],
];

$custom = [];
if (file_exists(CUSTOMPATH . 'modules/Admin/Config/modules.php')) {
    $custom = include CUSTOMPATH . 'modules/Admin/Config/modules.php';
}

return array_merge($custom, $module);