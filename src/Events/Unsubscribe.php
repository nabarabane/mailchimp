<?php

namespace MailChimp\Events;

class Unsubscribe extends \MailChimp\Event
{
	public $id;
	public $email;
	public $email_type;
	public $action;
	public $reason;
	public $ip_opt;
	public $campaign_id;
	public $merges_EMAIL;
	public $merges_FNAME;
	public $merges_LNAME;
	public $merges_INTERESTS;
}
