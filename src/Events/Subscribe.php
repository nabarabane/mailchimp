<?php

namespace MailChimp\Events;

class Subscribe extends \MailChimp\Event
{
	public $id;
	public $email;
	public $email_type;
	public $ip_opt;
	public $ip_signup;
	public $merges_EMAIL;
	public $merges_FNAME;
	public $merges_LNAME;
	public $merges_INTERESTS;
}
