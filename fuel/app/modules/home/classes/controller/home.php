<?php
namespace Home;
use Home\Model_Atcoderproblems;
use Home\Model_Atcoder;
use Home\Model_Db;

class Controller_Home extends \Controller
{
	const DIFF_UNKNOWN = -1;
	const DIFF_STEP = 400; // AtCoderの難易度の刻み
	const ATCODER_CONTESTS_BASE = 'https://atcoder.jp/contests/';

	public function before()
	{
		if (!\Auth::check()) return \Response::redirect('/auth/login');
	}

    public function post_import() // 右上のfetchボタンから呼び出す
	{
		$validate = function ($id) {
			$result = preg_match('/^(abc[0-9]{3}|[0-9]{3})$/i', $id);
			if (!$result) return \Response::redirect('/home');
			$valid_id = preg_match('/^[0-9]{3}$/', $id) // 英小文字+数字の形に正規化（例: abc440）
				? 'abc' . $id
				: (strtolower(substr($id, 0, 3)) . substr($id, 3));
			return $valid_id; 
		};
		$update_db_from_atcoder_fetch = function ($contest_id, $user_id) {
			$atcoder_username = Model_Db::get_target_atcoder_user($user_id); // ログイン中のユーザからAtCoderのユーザ名を取得
			$new_data = Model_Atcoder::fetch_submissions($contest_id, $atcoder_username); // AtCoderのサイトからスクレイピング
			Model_Db::update_db_from_atcoder_fetch($new_data);
		};

		$input_id = \Input::post('contest_id', '');
		$contest_id = $validate($input_id); // フロントでもバリデーションを行うが，念のためバックでもバリデーション
		list(, $user_id) = \Auth::get_user_id();
		if ($user_id === null) return \Response::redirect('/home'); // user_idが取得できない場合はリダイレクト << beforeでログインチェックしてるから取れるはずだが一応．
		$update_db_from_atcoder_fetch($contest_id, $user_id);
		return \Response::redirect('/home');
	}

	public function get_index()
	{
		$view = array();
		$view['header'] = \View::forge('myapp/header');
		$view['content'] = \View::forge('content', array('contests' => array()));
		return \Response::forge(\View::forge('myapp/base', $view));
	}

	public function get_get_contests() // JSON形式でコンテストデータを返す (非同期読み込み用)
	{
		$build_home_contents = function ($user_id) {
			$atcoder_username = Model_Db::get_target_atcoder_user($user_id); // DBから, 利用者が購読しているAtCoderのユーザ名を取得
			$view_data = Model_Atcoderproblems::get_all_problems(); // AtCoderProblemsのAPIから, コンテスト情報を取得 (コンテスト名，問題名，難易度など)
			$user_all_submissions = $atcoder_username ? Model_Atcoderproblems::fetch_submissions($atcoder_username) : array(); // AtCoderProblemsのAPIから, 最新のユーザ提出結果を取得
			Model_Db::update_db($user_all_submissions, $view_data, $atcoder_username); // DBを最新状態に更新
			$state_map = Model_Db::get_state_map($atcoder_username); // DBから, 最新情報を取得 <提出結果>
			$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
			foreach ($view_data as &$row) {
				$contest_name = isset($row['name']) ? $row['name'] : '';
				if ($contest_name === '') continue;
				$row['contest_url'] = self::contest_url($contest_name);
				foreach ($letters as $letter) {
					if (!isset($row[$letter])) continue;
					$problem_id = $contest_name . '_' . strtolower($letter);
					if (isset($state_map[$problem_id])) $row[$letter]['state'] = $state_map[$problem_id];
					$diff = isset($row[$letter]['diff']) ? $row[$letter]['diff'] : self::DIFF_UNKNOWN;
					$state = isset($row[$letter]['state']) ? $row[$letter]['state'] : '';
					$row[$letter]['problem_url'] = self::problem_url($contest_name, $letter);
					$row[$letter]['diff_class'] = self::diff_class($diff);
					$row[$letter]['diff_circle_style'] = self::render_diff_circle_style($diff);
					$row[$letter]['state_class'] = self::state_class($state);
					$row[$letter]['diff_display'] = ($diff === self::DIFF_UNKNOWN || $diff === null) ? '?' : (string) $diff;
				}
			}
			unset($row); // 参照渡しの事故防止のため
			return $view_data;
		};

		list(, $user_id) = \Auth::get_user_id();
		$contests = $build_home_contents($user_id);
		return \Response::forge(\Format::forge($contests)->to_json(), 200, array(
			'Content-Type' => 'application/json; charset=utf-8'
		));
	}

	private static function diff_class($diff)
	{
		$step = self::DIFF_STEP;
		if ($diff < 0 || $diff === null) return 'diff-gray';
		if ($diff < $step * 1) return 'diff-gray';
		if ($diff < $step * 2) return 'diff-brown';
		if ($diff < $step * 3) return 'diff-green';
		if ($diff < $step * 4) return 'diff-cyan';
		if ($diff < $step * 5) return 'diff-blue';
		if ($diff < $step * 6) return 'diff-yellow';
		if ($diff < $step * 7) return 'diff-orange';
		return 'diff-red';
	}

	private static function diff_color($diff)
	{
		$step = self::DIFF_STEP;
		if ($diff < 0 || $diff === null) return 'var(--diff-unknown)';
		if ($diff < $step * 1) return '#808080';
		if ($diff < $step * 2) return '#804000';
		if ($diff < $step * 3) return '#008000';
		if ($diff < $step * 4) return '#00c0c0';
		if ($diff < $step * 5) return '#0000ff';
		if ($diff < $step * 6) return '#c0c000';
		if ($diff < $step * 7) return '#ff8000';
		return '#ff0000';
	}

	private static function diff_percent($diff)
	{
		if ($diff < 0 || $diff === null) return 0;
		$step = self::DIFF_STEP;
		$percent = ($diff % $step) / $step * 100;
		if ($diff > 0 && $diff % $step == 0) $percent = 100;
		return $percent;
	}

	private static function render_diff_circle_style($diff) // viewに任せてもいい気はする
	{
		$color = self::diff_color($diff);
		$percent = self::diff_percent($diff);
		return "background: linear-gradient(to top, {$color} {$percent}%, transparent {$percent}%); border: 2px solid {$color};";
	}

	private static function contest_id($contest_name)
	{
		if ($contest_name === null || $contest_name === '') return null;
		if (preg_match('/ABC(\d+)/i', $contest_name, $matches)) {
			return 'abc' . $matches[1];
		}
		return null;
	}

	private static function contest_url($contest_name)
	{
		$id = self::contest_id($contest_name);
		return $id ? self::ATCODER_CONTESTS_BASE . $id : '#';
	}

	private static function problem_url($contest_name, $problem_letter)
	{
		$id = self::contest_id($contest_name);
		if ($id === null) return '#';
		$letter = strtolower($problem_letter);
		return self::ATCODER_CONTESTS_BASE . $id . '/tasks/' . $id . '_' . $letter;
	}

	private static function state_class($state)
	{
		if ($state === 'AC') return 'state-ac';
		if ($state === 'WA') return 'state-wa';
		return '';
	}
}
