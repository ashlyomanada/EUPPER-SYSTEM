<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class NotifController extends BaseController
{
    public function notifyAdmin()
    {
       
        $json = $this->request->getJSON();
        $userName = $json->UserName;
        $office = $json->Office;
        $fcmToken = $json->FcmToken;
        $month = $json->Month;
        $year = $json->Year;
        $level = $json->Level;

        $firebase = (new Factory)->withServiceAccount(WRITEPATH . 'firebase/firebase_credentials.json');
        $messaging = $firebase->createMessaging();

        $messageBody = "Officer {$userName} has submitted their rating for the period of {$month} {$year}.";
        $title = "New Rating Submission for {$level}";

        $notification = [
            'title' => $title,
            'body' => $messageBody,
        ];

        $message = CloudMessage::withTarget('token', $fcmToken)
            ->withNotification($notification);

        try {
            $messaging->send($message);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Notification sent']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}