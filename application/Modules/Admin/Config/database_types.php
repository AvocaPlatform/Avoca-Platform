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
    'default' => [
        'int',
        'varchar',
        'float',
        'double',
        'text',
        'date',
        'datetime',
    ],
    'defined' => [
        'id' => '%s INT 10 unsigned:true auto_increment:true',
        'num' => '%s INT 10',
        'relate' => '%s INT 10',
        'enum' => '%s VARCHAR 255',
    ],
];