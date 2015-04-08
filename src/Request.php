<?php

namespace MailChimp;

class Request
{
	private $key;
	private $dc;
	private $method;
	private $result;
	private $opts;

	public function __construct($method = null, $opts = null)
	{
		$file = __DIR__.'/../mailchimp.key';
		if (!@file_exists($file)) {
			throw new Exception('Отсутсвует файл с ключом.');
		}

		$this->key = trim(file_get_contents($file));
		$dc = 'us1';
		if (strstr($this->key, '-')) {
			list($key, $dc) = explode('-', $this->key, 2);
			if (!$dc) {
				$dc = 'us1';
			}
		}

		$this->dc = $dc;

		if ($method !== null) {
			$this->setMethod($method);
		}

		if ($opts !== null) {
			$this->setOpts($opts);
		}
	}

	public function setMethod($method)
	{
		$this->method = (string)$method;
	}

	public function setOpts($opts)
	{
		$this->opts = $opts;
		$this->opts['apikey'] = $this->key;
	}

	public function request()
	{
		if (!$this->key) {
			throw new Exception('Не задан API-ключ.');
		}

		if (!$this->method) {
			throw new Exception('Вы должны указать метод.');
		}

		if (!$this->opts || !is_array($this->opts)) {
			throw new Exception('Некорректные параметры запроса.');
		}

		$opts = json_encode($this->opts);
		$url = 'https://'.$this->dc.'.api.mailchimp.com/2.0/'.$this->method.'.json';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $opts);

		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$error = curl_error($ch);

		curl_close($ch);

		if ($error) {
			throw new Exception($error);
		}

		$this->result = json_decode($result);

		if (floor($info['http_code'] / 100) >= 4) {
			throw $this->catchError();
		}

		return $this->result;
	}

	private function catchError()
	{
		if ($this->result->status !== 'error' || !$this->result->name) {
			throw new Exception('We received an unexpected error: '.$this->result);
		}

		return new Exception($this->result->error, $this->result->code);
	}
}
