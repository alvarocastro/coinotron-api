<?php

class Curl
{
	private $handler;

	public function __construct ()
	{
		$this->handler = curl_init();
		//echo __DIR__ . '/cookies.txt';echo'<hr>';
		$this->setOptions(array(
			'cookiejar' => '/tmp/cookiestes',//__DIR__ . '/cookies.txt',
			'cookiefile' => '/tmp/cookiestes',//__DIR__ . '/cookies.txt',
			'followlocation' => 1,
			'returntransfer' => 1,
			'ssl_verifyhost' => 0,
			'ssl_verifypeer' => 0,
		));
	}

	public function setOption ($name, $value)
	{
		$name = constant('CURLOPT_' . strtoupper($name));
		curl_setopt($this->handler, $name, $value);
		return $this;
	}

	public function setOptions ($options)
	{
		foreach ($options as $name => $value) {
			$this->setOption($name, $value);
		}
		return $this;
	}

	public function exec ()
	{
		return curl_exec($this->handler);
	}

	public function get ()
	{
		return $this
			->setOption('httpget', true)
			->exec();
	}

	public function post ($values = array())
	{
		return $this
			->setOption('post', true)
			->setOption('postfields', http_build_query($values, '', '&'))
			->exec();
	}

	public function error ()
	{
		return curl_error($this->handler);
	}
}