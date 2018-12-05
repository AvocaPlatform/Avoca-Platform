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

class Emails extends AVC_ManageController
{
    protected $model = 'mailsend';

    public function save($ajax = null)
    {
        $this->disableView();

        if ($this->isPost()) {
            $post = $this->getPost();

            if ($post['to'] && $post['subject'] && $post['message']) {
                /** @var Mailsend $mailModel */
                $mailModel = $this->getModel();
                $status = $mailModel->sendMail($post['to'], $post['subject'], $post['message']);
                if ($status['mail_id']) {
                    if ($status['error']) {
                        $this->setError($mailModel->getErrors());
                    } else {
                        $this->setSuccess('Send mail is successful');
                    }

                    $this->manage_redirect('/emails/record/' . $status['mail_id']);
                } else {
                    $this->setError('Invalid parameters');
                    $this->manage_redirect('/emails');
                }
            }
        }
    }
}
