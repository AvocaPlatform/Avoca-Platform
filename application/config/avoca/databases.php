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

return array (
  'users' => 
  array (
    'name' => 'users',
    'ENGINE' => 'InnoDB',
    'fields' => 
    array (
      0 => 'id INT 10 unsigned:true auto_increment:true',
      1 => 'date_created DATETIME',
      2 => 'username VARCHAR 255',
      3 => 'password CHAR 32',
      4 => 'is_admin TINYINT 1 default:0',
    ),
    'indexes' => 
    array (
      0 => 'PK id',
      1 => 'UNIQUE username username',
    ),
  ),
  'emails' => 
  array (
    'name' => 'emails',
    'ENGINE' => 'InnoDB',
    'fields' => 
    array (
      0 => 'id INT 10 unsigned:true auto_increment:true',
      1 => 'date_created DATETIME',
      2 => 'from  VARCHAR 255',
      3 => 'to  VARCHAR 255',
      4 => 'subject VARCHAR 255',
      5 => 'message TEXT',
      6 => 'attachments TEXT',
    ),
    'indexes' => 
    array (
      0 => 'PK id',
    ),
  ),
);
