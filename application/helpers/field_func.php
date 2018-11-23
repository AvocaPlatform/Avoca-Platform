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

function fieldForm_readonly($field, $value, $option = []) {
    $extra = $option;
    $extra['readonly'] = 'readonly';
    return form_input($field, $value, $extra);
}

function fieldForm_disabled($field, $value, $option = []) {
    $extra = $option;
    $extra['disabled'] = 'disabled';
    return form_input($field, $value, $extra);
}