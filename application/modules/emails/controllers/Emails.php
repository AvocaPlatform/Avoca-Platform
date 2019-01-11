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

use Avoca\Controllers\AvocaManageController;

class Emails extends AvocaManageController
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

                // AJAX
                if ($ajax == 1) {
                    if ($status['mail_id']) {
                        return $this->jsonData([
                            'error' => 0,
                            'message' => $this->lang->line('Send email success'),
                            'id' => $status['mail_id'],
                        ]);
                    }

                    return $this->jsonData([
                        'error' => 1,
                        'message' => $mailModel->getErrors(),
                    ]);
                }

                if ($status['mail_id']) {
                    if ($status['error']) {
                        $this->setError($mailModel->getErrors());
                    } else {
                        $this->setSuccess('Send mail is successful');
                    }

                    $this->redirect('/emails/record/' . $status['mail_id']);
                } else {
                    $this->setError('Invalid parameters');
                    $this->redirect('/emails');
                }
            }

            if ($ajax == 1) {
                return $this->jsonData([
                    'error' => 1,
                    'message' => 'Invalid param',
                ]);
            }
        }
    }
}
