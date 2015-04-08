<?php

namespace MailChimp;

class Event
{
	public $type;
	public $fired_at;
	public $list_id;

	protected $webhook;
	protected $data;

	const Subscribe = 'subscribe';
	const Unsubscribe = 'unsubscribe';
	const ProfileUpdate = 'profile';
	const EmailChange = 'upemail';
	const EmailDelete = 'cleaned';
	const CampaignStatus = 'campaign';

	public function __construct($hook, $data)
	{
		$this->webhook = $hook;
		$this->type = $data['type'];
		$this->fired_at = $data['fired_at'];
		$this->list_id = $data['data']['list_id'];
		$this->data = $data['data'];

		foreach ($this->data as $key => $value) {
			if (!is_array($value)) {
				$this->{$key} = $value;
			} else {
				foreach ($value as $k => $v) {
					$this->{$key.'_'.$k} = $v;
				}
			}
		}
	}

	public static function factory()
	{
		$data = $_GET;

		if (!$data || !isset($data['type']) || !isset($data['fired_at']) || !isset($data['data']['list_id'])) {
			return false;
		}

		$t = $data['type'];
		$r = new \ReflectionClass('\\MailChimp\\Event');
		$cs = $r->getConstants();
		$c = null;
		foreach ($cs as $n => $v) {
			if ($v == $t) {
				$c = $n;
				break;
			}
		}

		if ($c == null) {
			return false;
		}

		$list_id = $data['data']['list_id'];
		$class = '\\MailChimp\\Events\\'.$c;
		$request = new \MailChimp\Request('lists/webhooks', ['id' => $list_id]);

		try {
			$result = $request->request();
		} catch (Exception $e) {
			return false;
		}

		foreach ($result as $webhook) {
			$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if ($url == $webhook->url) {
				$hook = $webhook;
				break;
			}
		}

		if (!isset($hook)) {
			return false;
		}

		return new $class($hook, $data);
	}
}
