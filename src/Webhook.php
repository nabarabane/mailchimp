<?php

namespace MailChimp;

class Webhook
{
	private $hooks;

	public function __construct()
	{
		$this->hooks = [];
	}

	private function getCallbacks($name)
	{
		return isset($this->hooks[$name]) ? $this->hooks[$name] : [];
	}

	public function on($name, $callback)
	{
		if (!is_callable($callback, true)) {
			throw new \InvalidArgumentException(sprintf('Invalid callback: %s.', print_r($callback, true)));
		}

		if (!is_array($name)) {
			$name = [$name];
		}

		foreach ($name as $event) {
			$this->hooks[$event][] = $callback;
		}
	}

	public function listen()
	{
		if (!$listen = (isset($_POST['type']) || !isset($_POST['fired_at']))) {
			return false;
		}

		$data = $_POST;
		$event = $data['type'];
		$list_id = $data['data']['list_id'];

		try {
			$result = (new Handler())->request('lists/webhooks', ['id' => $list_id])->result;
		} catch (\Exception $e) {
			return false;
		}

		$listen = false;
		foreach ($result as $webhook) {
			$url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if ($url == $webhook->url) {
				$listen = true;
				break;
			}
		}

		if (!$listen) {
			return false;
		}

		foreach($this->getCallbacks($event) as $callback) {
			call_user_func($callback, $data['data']);
		}
	}
}
