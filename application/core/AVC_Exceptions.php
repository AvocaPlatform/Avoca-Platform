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

class AVC_Exceptions extends CI_Exceptions
{
    public function getErrorTemplatePath()
    {
        $templates_path = config_item('error_views_path');
        if (empty($templates_path)) {
            $templates_path = VIEWPATH . 'avoca' . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR;
        }

        return $templates_path;
    }

    public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        $templates_path = $this->getErrorTemplatePath();

        if (is_cli()) {
            $message = "\t" . (is_array($message) ? implode("\n\t", $message) : $message);
            $template = 'cli' . DIRECTORY_SEPARATOR . $template;
        } else {
            set_status_header($status_code);
            $message = '<p>' . (is_array($message) ? implode('</p><p>', $message) : $message) . '</p>';
            $template = 'html' . DIRECTORY_SEPARATOR . $template;
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }
        ob_start();
        include($templates_path . $template . '.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    public function show_php_error($severity, $message, $filepath, $line)
    {
        $templates_path = $this->getErrorTemplatePath();

        $severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;

        // For safety reasons we don't show the full file path in non-CLI requests
        if (!is_cli()) {
            $filepath = str_replace('\\', '/', $filepath);
            if (FALSE !== strpos($filepath, '/')) {
                $x = explode('/', $filepath);
                $filepath = $x[count($x) - 2] . '/' . end($x);
            }

            $template = 'html' . DIRECTORY_SEPARATOR . 'error_php';
        } else {
            $template = 'cli' . DIRECTORY_SEPARATOR . 'error_php';
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }

        ob_start();
        include($templates_path . $template . '.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }

    public function show_exception($exception)
    {
        $templates_path = $this->getErrorTemplatePath();

        if (empty($templates_path)) {
            $templates_path = VIEWPATH . 'errors' . DIRECTORY_SEPARATOR;
        }

        $message = $exception->getMessage();
        if (empty($message)) {
            $message = '(null)';
        }

        if (is_cli()) {
            $templates_path .= 'cli' . DIRECTORY_SEPARATOR;
        } else {
            $templates_path .= 'html' . DIRECTORY_SEPARATOR;
        }

        if (ob_get_level() > $this->ob_level + 1) {
            ob_end_flush();
        }

        ob_start();
        include($templates_path . 'error_exception.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
    }
}