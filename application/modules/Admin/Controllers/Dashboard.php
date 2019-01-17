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

use Avoca\Controllers\AvocaAdminController;

class Dashboard extends AvocaAdminController
{
    public function index()
    {
        $this->data = [
            'status' => 1
        ];
    }
}