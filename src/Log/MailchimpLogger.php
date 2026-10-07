<?php

namespace NewsletterSignupKit\Log;

use ClassKit\Log\Logger;

class MailchimpLogger extends Logger
{
    public function __construct()
    {
        parent::__construct('mailchimp');
    }
}
