<?php

namespace MailChimp\Events;

class EmailChange extends \MailChimp\Event
{
	public $new_id;
	public $new_email;
	public $old_email;
}
