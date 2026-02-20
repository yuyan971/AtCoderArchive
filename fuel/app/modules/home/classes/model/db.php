<?php
namespace Home;

class Model_Db extends \Model
{
	private static function normalize_language($raw_language_str)
	{
		$tmp = trim((string) $raw_language_str);
		if (($pos = strpos($tmp, '(')) !== false) $result = trim(substr($tmp, 0, $pos));

		if (strncmp($result, 'C++', 3) === 0) return 'C++'; // "C++" から始まるものは全部 "C++" にする（例: C++23, C++17）
		return $result;
	}

	public static function get_state_map($atcoder_username)
	{
		if ($atcoder_username === null || $atcoder_username === '') return array(); // 未ログインの場合は空配列を返す

		$row = \DB::select('id')
			->from('atcoder_users')
			->where('user_name', '=', $atcoder_username)
			->execute()
			->as_array();
		if (empty($row)) return array(); // 起こるわけない．
        $atcoder_username = null; // 不要になったため
		$atcoder_user_id = (int) $row[0]['id']; // atcoder_usersテーブルにおける内部ID

		$pairs = \DB::select(array('problems.problem_id', 'problem_id'), array('submissions.result', 'result')) // abc441_a と AC などのペアの配列
			->from('submissions')
			->join('problems', 'INNER')->on('submissions.problem_id', '=', 'problems.id')
			->where('submissions.atcoder_user_id', '=', $atcoder_user_id)
			->execute()
			->as_array();

		// 優先度: AC=3, WA=2, その他=1。同一 problem_id は優先度の高い方を残す
		$priority_map = array('AC' => 3, 'TLE' => 2);
		$state_map = array();
		foreach ($pairs as $pair) {
			$problem_id = isset($pair['problem_id']) ? $pair['problem_id'] : '';
			$result = isset($pair['result']) ? $pair['result'] : '';
			if ($problem_id === '') continue; // ここで弾かれるものは存在しないはず (result='' は許容する)
			$pri = isset($priority_map[$result]) ? $priority_map[$result] : 1; // AC=3, TLE=2, その他=1
            if (isset($state_map[$problem_id]) && ($state_map[$problem_id] === 'AC' || ($state_map[$problem_id] === 'TLE' && $result !== 'AC'))) continue; // 更新する必要がない場合はスキップ
			$state_map[$problem_id] = $result;
		}
		return $state_map;
	}

	public static function get_target_atcoder_user($user_id)
	{
		$target_name = \DB::select('atcoder_users.user_name')
			->from('subscriptions')
			->join('atcoder_users', 'INNER')->on('subscriptions.atcoder_user_id', '=', 'atcoder_users.id')
			->where('subscriptions.user_id', '=', (int) $user_id)
			->execute()
			->as_array();
		if (empty($target_name)) {
			return null;
		}
		return $target_name[0]['user_name'];
	}

	public static function update_db($user_all_submissions, $all_problems, $username)
	{
        $update_problems_table = function ($input) {
            $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
		    foreach ($input as $contest_data) {
			    $contest_id = isset($contest_data['name']) ? $contest_data['name'] : ''; // abc334 とか abc221 とか
			    if ($contest_id === '') continue; // そんなものが存在するはずがないが，念のため
			    foreach ($letters as $letter) {
				    if (!isset($contest_data[$letter]) || (isset($contest_data[$letter]['name']) && $contest_data[$letter]['name'] === '')) continue;
				    $problem_id_str = $contest_id . '_' . strtolower($letter); // abc331_a とか abc221_b とか
				    $difficulty = isset($contest_data[$letter]['diff']) ? (int) $contest_data[$letter]['diff'] : null; // 10 とか 2324 とか

				    $existing = \DB::select('id')->from('problems')->where('problem_id', '=', $problem_id_str)->execute()->as_array();
				    if (!empty($existing)) { // すでに存在する場合... -> 更新
					    \DB::update('problems')->set(array('difficulty' => $difficulty))->where('problem_id', '=', $problem_id_str)->execute(); // 正直，更新は必要ない気がする．
				    } else { // そんな問題なんて存在しない場合... -> 新規作成
					    \DB::insert('problems')->set(array('problem_id' => $problem_id_str, 'difficulty' => $difficulty))->execute();
				    }
			    }
		    }
	    };

        $update_problems_table($all_problems);
        $all_problems = null; // 不要になったため

        $tmp = \DB::select('id')->from('atcoder_users')->where('user_name', '=', $username)->execute()->as_array();
		$atcoder_users_table_id = (int) $tmp[0]['id'];

        $problem_id_strs = array();
        foreach ($user_all_submissions as $submission) {
		    $pid = isset($submission['problem_id']) ? $submission['problem_id'] : ''; // abc441_a とか abc221_b とか
		    if ($pid !== '') $problem_id_strs[$pid] = '_'; // 中の値を使用することはない．
	    }
	    if (empty($problem_id_strs)) return; // 提出回数=0といったところか？..
        $problem_rows = \DB::select('id', 'problem_id')->from('problems')->where('problem_id', 'IN', array_keys($problem_id_strs))->execute()->as_array();
        $problem_id_to_internal = array(); // APIから取得した提出一覧に含まれるproblem_id → problemsテーブルのid のマップ
        foreach ($problem_rows as $pr) {
            $problem_id_to_internal[$pr['problem_id']] = (int) $pr['id']; // [abc441_a] => 1 とか [abc221_b] => 2 とか 
        }

        foreach ($user_all_submissions as $submission) {
		    $submission_id = isset($submission['submission_id']) ? $submission['submission_id'] : null; // 1234567890 とか
		    if ($submission_id === null || $submission_id === '') continue;
            $problem_id_str = isset($submission['problem_id']) ? $submission['problem_id'] : ''; // abc441_a とか abc221_b とか
		    $result = isset($submission['result']) ? $submission['result'] : '';
		    $language = isset($submission['language']) ? $submission['language'] : '';
			$language = self::normalize_language($language);
		    if ($problem_id_str === '') continue; // まだ登録にない問題の場合はスキップ．
	        if (!isset($problem_id_to_internal[$problem_id_str])) continue; // そんなはずないんだが，念のため
            $problem_internal_id = $problem_id_to_internal[$problem_id_str]; // 1 とか 2 とか
            $submission_id_str = (string) $submission_id; // 1234567890 とか

            $existing = \DB::select('id')->from('submissions')->where('submission_id', '=', $submission_id_str)->execute()->as_array(); // すでに存在する場合... -> 更新
            if (!empty($existing)) { 
			    \DB::update('submissions')->set(array('result' => $result, 'language' => $language))->where('submission_id', '=', $submission_id_str)->execute(); // 果たして，更新する意味はあるのか..？
		    } else {
			    \DB::insert('submissions')->set(array(
			        'submission_id'   => $submission_id_str,
			        'atcoder_user_id' => $atcoder_users_table_id,
			        'problem_id'      => $problem_internal_id,
			        'result'          => $result,
			        'language'        => $language,
			    ))->execute();
            }
        }
	}
}
