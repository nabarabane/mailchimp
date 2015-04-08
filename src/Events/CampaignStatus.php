<?php

namespace MailChimp\Events;

class CampaignStatus extends \MailChimp\Event
{
	public $id;
	public $subject;
	public $status;
	public $reason;
}
