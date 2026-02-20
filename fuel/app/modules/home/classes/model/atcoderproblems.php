<?php
namespace Home;

class Model_Atcoderproblems extends \Model
{
	const BASE_URL = 'https://kenkoooo.com/atcoder/resources/';
	const CONTESTS_JSON = 'contests.json';
	const PROBLEMS_JSON = 'problems.json';
	const DIFF_JSON = 'problem-models.json';

	const SUBMISSIONS_API_URL_PREFIX = 'https://kenkoooo.com/atcoder/atcoder-api/v3/user/submissions?user=';
	const SUBMISSIONS_API_URL_SUFFIX = '&from_second=0';
	const DIFF_NOT_SET = -1;
	const STATE_NOT_SET = '';
	const DEFAULT_ABC_LIMIT = 500;
	const HTTP_TIMEOUT = 15;
	const CACHE_TTL = 3; // 単位：秒 (キャッシュ有効時間)

	const CONTEST_ID_ALIASES = array( // problems.json の contest_id が誤っている場合の正しいIDへのマップ
		'jsc2025advance-final' => 'abc422',
	);
	private static function normalize_contest_id($contest_id) // problems.json の contest_id が誤っている場合があるので，それへの対策
	{
		if ($contest_id === '') return '';
		$aliases = self::CONTEST_ID_ALIASES;
		return isset($aliases[$contest_id]) ? $aliases[$contest_id] : $contest_id;
	}

	private static function fetch_func($url)
	{
		$ctx = stream_context_create(array(
			'http' => array(
				'timeout' => self::HTTP_TIMEOUT,
				'header'  => "Accept-Encoding: gzip\r\n",
			),
		));
		$raw_data = file_get_contents($url, false, $ctx);
		$data = gzdecode($raw_data);
		return json_decode($data, true);
	}

	public static function fetch_submissions($target_atcoder_username)
	{
		$cache_key = 'home.atcoderproblems.submissions.' . $target_atcoder_username;
		try {
			$cached = \Cache::get($cache_key);
			if ($cached === true) return $cached;
		} catch (\Exception $e) {}
		$data = static::fetch_submissions_data($target_atcoder_username);
		\Cache::set($cache_key, $data, self::CACHE_TTL);
		return $data;
	}
	private static function fetch_submissions_data($user_name)
	{
		$user_submissions_data = static::fetch_func(self::SUBMISSIONS_API_URL_PREFIX . $user_name . self::SUBMISSIONS_API_URL_SUFFIX);
		$data = array();
		if (is_array($user_submissions_data)) { // 提出数がない場合とか，APIが正常に動いていない場合は空配列を返す
			foreach ($user_submissions_data as $submission) {
				$contest_id = isset($submission['contest_id']) ? $submission['contest_id'] : '';
				if ($contest_id === '' || ! preg_match('/^abc\d+$/i', $contest_id)) continue;

				$language = isset($submission['language']) ? $submission['language'] : '';

				$data[] = array(
					'submission_id' => isset($submission['id']) ? $submission['id'] : null,
					'problem_id'    => isset($submission['problem_id']) ? $submission['problem_id'] : '',
					'contest_id'    => $contest_id,
					'language'      => $language,
					'result'        => isset($submission['result']) ? $submission['result'] : '',
					'epoch_second'  => isset($submission['epoch_second']) ? (int) $submission['epoch_second'] : null,
				);
			}
		}
		return $data;
	}

	public static function get_all_problems($abc_limit=null)
	{
		$cache_key = 'home.atcoderproblems.' . ($abc_limit === null ? 'all' : (int) $abc_limit);
		try {
			$cached = \Cache::get($cache_key);
			if ($cached === true) return $cached;
		} catch (\Exception $e) {}
		$view_data = static::fetch_and_build_data($abc_limit);
		\Cache::set($cache_key, $view_data, self::CACHE_TTL);
		return $view_data;
	}
	private static function fetch_and_build_data($abc_limit)
	{
		$contests_data = static::fetch_func(self::BASE_URL . self::CONTESTS_JSON);
		$problems_data = static::fetch_func(self::BASE_URL . self::PROBLEMS_JSON);
		$diff_data = static::fetch_func(self::BASE_URL . self::DIFF_JSON);
		//== データの取得完了 ==

		$contest_list = array(); // abc440 とかがキーの配列
		foreach ($contests_data as $e) {
			if (empty($e['id']) || ! preg_match('/^abc\d+$/i', $e['id'])) continue; // abc444444 とかも通過するが，よしとする．
			
			$contest_list[$e['id']] = array(
				'start_epoch_second'  => isset($e['start_epoch_second']) ? (int) $e['start_epoch_second'] : 0, // 値が大きいほど，最新のコンテスト
			);
		}
		uasort($contest_list, function ($a, $b) { // 0:abc441 → 1:abc440 → 2:abc439 → ... (例)
			return $b['start_epoch_second'] - $a['start_epoch_second'];
		});
		if ($abc_limit !== null) $contest_list = array_slice($contest_list, 0, $abc_limit, true); // キーの組み合わせは変えないという意味のtrue | 先頭から$abc_limit個の要素を取得 | limitなしなら素通り
		$problem_list = array();
		foreach ($problems_data as $e) {
			$cid = static::normalize_contest_id(isset($e['contest_id']) ? $e['contest_id'] : '');
			if ($cid === '' || ! isset($contest_list[$cid])) continue; // コンテストリストに存在しないコンテストの問題である場合はスキップ
			
			$idx = isset($e['problem_index']) ? $e['problem_index'] : ''; // AとかBとか
			if ($idx === '' || ! preg_match('/^[A-G]$/i', $idx)) continue; // A～Gの範囲でない場合はスキップ
			
			if ( ! isset($problem_list[$cid])) $problem_list[$cid] = array(); // まだそのコンテストの問題リストが存在しない場合は空の配列を作成
			
			$problem_list[$cid][$idx] = array(  // cid = abc440, idx = A とか
				'id'    => isset($e['id']) ? $e['id'] : '', // 問題ID abc440_c
				'title' => isset($e['title']) ? $e['title'] : '', // 問題名 B. Power Socket
			);
		}
		$view_data = array();
		$problem_letters = range('A', 'G');
		foreach ($contest_list as $contest_id => $contest_info) { // contest_id = abc440 とか
			$row = array(
				'name' => $contest_id,
			);
			$problems = isset($problem_list[$contest_id]) ? $problem_list[$contest_id] : array(); // 問題リスト [abc440_a] とか [abc440_b] とかのリスト（コンテストに問題が無い場合は空）
			foreach ($problem_letters as $letter) { // A, B, C, D, E, F, G
				if (!isset($problems[$letter])) {
					$row[$letter] = array(
						'name'  => '',
						'diff'  => self::DIFF_NOT_SET,
						'state' => self::STATE_NOT_SET,
					);
					continue;
				}
				$diff = self::DIFF_NOT_SET;
				$prob = $problems[$letter];
				$name = isset($prob['title']) ? $prob['title'] : '';
				$problem_id = isset($prob['id']) ? $prob['id'] : '';
				if (isset($diff_data[$problem_id]['difficulty'])) { // 割と存在しない(値がnull)場合があるので注意
					$d = $diff_data[$problem_id]['difficulty'];
					$diff = round( $d >= 400 ? $d : 400 / exp(1.0 - $d/400) ); // https://gist.github.com/sevenc-nanashi/f2fe6ede04e7b4c538cca715f9edd09f (固有計算式)
				}
				$row[$letter] = array(
					'name'  => $name,
					'diff'  => $diff,
					'state' => self::STATE_NOT_SET,
				);
			}
			$view_data[] = $row;
		}
		return $view_data;
	}
}
