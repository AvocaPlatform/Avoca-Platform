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

/**
 * Class Mailsend
 * @property CI_Email $email
 */
class Mailsend extends AVC_Model
{
    protected $table = 'emails';

    /**
     * @param $to
     * @param $subject
     * @param $message
     * @param array $extra
     * @return array
     */
    public function sendMail($to, $subject, $message, $extra = [])
    {
        $config = config_item('mail');
        $this->load->library('email', $config);

        if (!empty($extra['from'])) {
            $from = $extra['from'];
        } else {
            $from = config_item('mail_from');
        }

        $this->email->set_newline("\r\n");
        $this->email->from($from);
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($message);
        $status = $this->email->send();

        $statusSave = ($status) ? 1 : 0;

        $mailId = $this->save([
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'status' => $statusSave,
        ]);

        if($status) {
            return [
                'error' => 0,
                'mail_id' => $mailId
            ];
        } else {
            $this->errors[] = 'Error mail config';
            return [
                'error' => 1,
                'mail_id' => $mailId,
                'message' => $this->email->print_debugger()
            ];
        }
    }
}
