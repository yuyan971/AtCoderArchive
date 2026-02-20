<?php
namespace Home;

class Model_Atcoder extends \Model
{
	const HTTP_TIMEOUT = 15;
	const BASE_URL = 'https://atcoder.jp/contests/';
	const SUBMISSIONS_URL = '/submissions?f.User=';

	public static function fetch_submissions($contest_id, $username) // スクレイピングで，提出一覧のhtmlを取得し，パースして，連想配列のリストにする
	{
		$get_session_key = function () {
			\Config::load('atcoder_session', true);
			return \Config::get('atcoder_session.revel_session', '');
		};
		$fetch_html = function ($url, $session_key) {
			$ctx = stream_context_create(array(
				'http' => array(
					'timeout' => self::HTTP_TIMEOUT,
					'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n" . "Cookie: REVEL_SESSION=" . $session_key() . "\r\n",
				),
			));
			return @file_get_contents($url, false, $ctx);
		};

		$url = self::BASE_URL . $contest_id . self::SUBMISSIONS_URL . urlencode($username);
		$html = $fetch_html($url, $get_session_key);
		if ($html === false || $html === '') return array();
		$new_data = self::parse_submissions_table($html, $contest_id);
		return $new_data;
	}

	private static function parse_submissions_table($html, $contest_id) // 不正なhtmlが入る可能性もある (例えば，ログイン失敗時のhtmlなど)
	{
		// --- 1. HTML を DOM ツリーに変換する ---
		$doc = new \DOMDocument();
		$use_errors = libxml_use_internal_errors(true);  // HTML の文法エラーを画面上に出さず内部に溜める
		$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);
		libxml_use_internal_errors($use_errors);        // 元のエラー表示の設定に戻す

		// --- 2. XPath で「提出テーブルの行」だけを取得する ---
		$xpath = new \DOMXPath($doc);
		// 「class に table-responsive を含む div の内側にある、tbody 直下の tr」＝提出1行ずつ
		$rows = $xpath->query('//div[contains(@class,"table-responsive")]//tbody/tr');
		if ($rows === null || $rows->length === 0) return array();  // 表がない or 行が0件なら空配列
		
		$result = array();
		$base_url = rtrim(self::BASE_URL, 'contests/');  // 相対URLを絶対URLにするときに使う

		// --- 3. 行ごとにループし、各列から値を取り出す ---
		for ($i = 0; $i < $rows->length; $i++) {
			$tr = $rows->item($i);           // 今の行（tr 要素）
			$tds = $xpath->query('td', $tr); // その行の中の td（セル）をすべて取得
			//\Log::warning("\n\ntds:\n\n" . print_r($tds->length, true), __METHOD__);
			if ($tds->length < 10) continue;  // 列数が足りない行はスキップ（想定外のレイアウト対策） 通常は10列だが，コンパイルエラー(CE)の場合は8列 << これによって，CEをスキップできる

			// 以下、この行から値を取り出すための「関数」を3つ定義（列番号でアクセスしやすくするため）
			// ・cell($index)      … 指定した列の「表示テキスト」を返す
			$cell = function ($index) use ($tds) {
				$node = $tds->item($index);
				return $node ? trim($node->textContent) : '';
			};
			// ・link_href($index)  … 指定した列の「最初の a タグの href」を返す
			$link_href = function ($index) use ($tds) {
				$node = $tds->item($index);
				if (!$node) return '';
				$el = $node->getElementsByTagName('a')->item(0);
				return $el && $el->hasAttribute('href') ? $el->getAttribute('href') : '';
			};
			// ・td_attr($index, $attr) … 指定した列の td 自体の属性（例: data-id）を返す
			$td_attr = function ($index, $attr) use ($tds) {
				$node = $tds->item($index);
				return $node && $node->hasAttribute($attr) ? $node->getAttribute($attr) : '';
			};

			// 列 1（0始まり）: 問題 → リンクの末尾が task_id、表示文が task_title
			$task_href = $link_href(1);
			$task_id = $task_href !== '' ? basename($task_href) : '';  // 例: /contests/abc440/tasks/abc440_b → abc440_b
			$task_title = $cell(1);

			// 列 5: 得点。td に data-id が付いていればそれが提出ID。表示数字が得点
			$submission_id = $td_attr(4, 'data-id');
			$score = (int) preg_replace('/\D/', '', $cell(4));  // 数字以外を消して整数に

			// 列 10: Detail のリンク（相対パスなので先頭にベースURLを付ける）
			$detail_path = $link_href(9);
			$detail_url = $detail_path !== '' ? $base_url . $detail_path : '';

			// この1行分を、キー名を決めた連想配列にしてリストに追加
			$result[] = array(
				'submitted_at'  => $cell(0),   // 列0: 提出日時
				'task_id'       => $task_id,
				'task_title'    => $task_title,
				'user'          => $cell(2),   // 列2: ユーザー名
				'language'      => $cell(3),   // 列3: 言語
				'score'         => $score,
				'submission_id' => $submission_id,
				'code_size'     => $cell(5),   // 列5: コード長
				'status'        => $cell(6),   // 列6: AC/WA など
				'exec_time'     => $cell(7),   // 列7: 実行時間
				'memory'        => $cell(8),   // 列8: メモリ
				'detail_url'    => $detail_url,
			);
		}
		return $result;
	}
}