<?php
/**
 * Created by Jacky.
 * Developer
 * Email: jacky@youaddon.com / hungtran@up5.vn
 * Phone: +84 972014011
 * Skype: tdhungit
 * Site: https://youaddon.com / https://up5.vn
 * Github: https://github.com/teamcarodev / https://github.com/youaddon
 * Facebook: https://www.facebook.com/jackytran0101
 */

namespace Custom\Modules\Home\Controllers;


use App\Modules\Home\Controllers\Home as HomeController;

class Home extends HomeController
{
    public function index()
    {
        $this->setTitle('Example Custom');
        parent::index();
    }
}