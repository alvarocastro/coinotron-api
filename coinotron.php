<?php

require_once __DIR__ . '/lib/curl.php';
require_once __DIR__ . '/lib/simple_html_dom.php';

class Coinotron
{
	const URL_LOGIN = 'https://coinotron.com/coinotron/AccountServlet?action=logon';
	const URL_ACCOUNT = 'https://coinotron.com/coinotron/AccountServlet?action=myaccount';

	const COIN_LTC = 'LTC';
	const COIN_FTC = 'FTC';
	const COIN_PPC = 'PPC';
	const COIN_TRC = 'TRC';
	const COIN_FRC = 'FRC';

	private $coins = array(
		self::COIN_LTC,
		self::COIN_FTC,
		self::COIN_PPC,
		self::COIN_TRC,
		self::COIN_FRC
	);


	public function __construct ($coins = null)
	{
		if ($coins) {
			if (!is_array($coins)) {
				$coins = array($coins);
			}

			$tmpCoins = array();
			foreach ($this->coins as $coin) {
				if (in_array($coin, $coins)) {
					$tmpCoins[] = $coin;
				}
			}
			$this->coins = $tmpCoins;
		}

		if (!$this->coins) {
			throw new Exception('No coins specified.', 1);
		}
	}

	public function login ($user, $password)
	{
		$c = new Curl();
		$c->setOption('url', self::URL_LOGIN);
		$html = $c->post(array(
			'name' => $user,
			'password' => $password,
		));

		//Website is unavailable for about 60 minutes due to database maintenance. Mining pools are up and running.
		if (substr($html, 0, 22) === 'Website is unavailable') {
			throw new Exception('Coinotron.com is unavailable.', 2);
		}

		$doc = str_get_html($html);
		if ($error = $doc->find('#content font[color="red"]', 0)) {
			echo $error->plaintext;die;
		}
	}

	public function getAccountData ()
	{
		$c = new Curl();
		$c->setOption('url', self::URL_ACCOUNT);
		$html = $c->get();

		$doc = str_get_html($html);

		$data = array();
		foreach ($this->coins as $coin) {
			$data[$coin] = array(
				'pool_info' => array(),
				'rewards' => array(),
				'last_payouts' => array(),
				'total_payouts' => array(),
			);
		}

		//* POOL INFO
		$poolinfo = $doc->find('#header_pool_info', 0);
		$rows = $poolinfo->find('table', 0)->find('tr');
		foreach ($rows as $row) {
			if (count($row->find('td')) !== 8) {
				continue;
			}

			$coin = trim($row->find('td', 0)->plaintext);
			if (!in_array($coin, $this->coins)) {
				continue;
			}

			$data[$coin]['pool_info'] = array(
				//'coin' => $coin,
				'speed' => trim($row->find('td', 1)->plaintext),
				'stratum' => (int)trim($row->find('td', 2)->plaintext) / 100,
				'miners' => (int)trim($row->find('td', 3)->plaintext),
				'round' => trim($row->find('td', 4)->plaintext),
				'exchange' => (float)trim($row->find('td', 5)->plaintext),
				'difficulty' => (int)trim($row->find('td', 6)->plaintext),
				'profitability' => (float)trim($row->find('td', 7)->plaintext),
			);
		}
		//*/
		
		//* REWARDS
		foreach ($this->coins as $coin) {
			$data[$coin]['rewards'] = array(
				'payout_address' => trim($doc->find('input[name="Wallet' . $coin . '"]', 0)->value),
				'payout_treshold' => (float)trim($doc->find('input[name="SendThreshold' . $coin . '"]', 0)->value),
				'hashrate' => trim($doc->find('input[name="CurrentRoundRewards' . $coin . '"]', 0)->value),
				'estimated_coins_day' => (float)trim($doc->find('input[name="UnconfirmedRewards' . $coin . '"]', 0)->value),
				'estimated_round_rewards' => (float)trim($doc->find('input[name="CurrentRoundRewards' . $coin . '"]', 1)->value),
				'unconfirmed_rewards' => (float)trim($doc->find('input[name="UnconfirmedRewards' . $coin . '"]', 1)->value),
				'confirmed_rewards' => (float)trim($doc->find('input[name="ConfirmedRewards' . $coin . '"]', 0)->value),
			);
		}
		//*/

		//* PAYOUTS
		$rows = $doc->find('.Internal', -1)->find('tr');
		foreach ($rows as $row) {
			if ($row->find('thead')) {
				continue;
			}

			$coin = trim($row->find('td', 1)->plaintext);
			if (!in_array($coin, $this->coins)) {
				continue;
			}

			if ($row->find('a')) { //payouts
				$data[$coin]['last_payouts'][] = array(
					'link' => trim($row->find('a', 0)->href),
					'date' => trim($row->find('a', 0)->plaintext),
					//'coin' => $coin,
					'amount' => (float)trim($row->find('td', 2)->plaintext),
				);
			} else { //totals
				$data[$coin]['total_payouts'] = array(
					//'coin' => $coin,
					'amount' => (float)trim($row->find('td', 2)->plaintext),
				);
			}
		}
		//*/

		return $data;
	}

	private function parsePoolInfo ($doc)
	{
	}
}