<?php

namespace MailChimp\Events;

class EmailDelete extends \MailChimp\Event
{
	public $campaign_id;
	public $reason;
	public $email;
}
