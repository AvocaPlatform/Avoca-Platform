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

class AVC_URI extends CI_URI
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Grab the requested file format fromt he URI
     * This requires an addition to the routes.php file:
     *
     * $route['(:any)/(:any)\.(:any)'] = "$1/$2";
     * @param $default_return
     * @param $separator
     * @return string
     */
    public function format($default_return = false, $separator = '.')
    {
        $format = $default_return;
        if (count($this->segments)) {
            $last_segment = explode($separator, end($this->segments));
            if (count($last_segment) > 1) {
                $format = strtolower(end($last_segment));
            }
        }
        return $format;
    }
}