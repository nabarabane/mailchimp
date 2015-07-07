<?php

namespace MailChimp;

class Handler
{
	private $key;
	private $dc;
	private $method;
	private $result;
	private $params = [];

	public $result;

	public function __construct()
	{
		$file = __DIR__.'/../mailchimp.key';
		if (!file_exists($file)) {
			throw new Exception('Отсутсвует файл с ключом');
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
		$this->params['apikey'] = $this->key;
	}

	public function setMethod($method)
	{
		$this->method = $method;

		return $this;
	}

	public function setOpts($params)
	{
		$this->params = array_merge($this->params, $params);

		return $this;
	}

	public function request()
	{
		if (!$this->method) {
			throw new Exception('Не указан метод запроса');
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://'.$this->dc.'.api.mailchimp.com/2.0/'.$this->method.'.json');
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->params));

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

		return $this;
	}

	private function catchError()
	{
		if ($this->result->status !== 'error' || !$this->result->name) {
			throw new Exception('We received an unexpected error: '.$this->result);
		}

		return new Exception($this->result->error, $this->result->code);
	}
}
