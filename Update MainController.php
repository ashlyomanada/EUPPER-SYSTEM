public function sendEmail()
{
    try {
        // Get JSON data from the request
        $formData = $this->request->getJSON();
        // Load the email library
        $email = \Config\Services::email();
        // Set email parameters
        $email->setTo('ashlyomanada@gmail.com');
        $email->setFrom($formData->sender);
        $email->setSubject('Request Form');
        $message = 'This is a request form from username: ' . $formData->username;
        $email->setMessage($message);
        // Send email
        if ($email->send()) {
            return $this->response->setJSON(['message' => 'Email sent successfully.']);
        } else {
            log_message('error', 'Email failed to send. Error: ' . $email->printDebugger(['headers']));
            return $this->response->setJSON(['error' => 'Email failed to send.']);
        }
