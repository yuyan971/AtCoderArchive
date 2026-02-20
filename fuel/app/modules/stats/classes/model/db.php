<?php
namespace Stats;

class Model_Db extends \Model
{
	// AtCoder難易度の閾値（色分け用）
	const DIFF_GRAY   = 400;
	const DIFF_BROWN  = 800;
	const DIFF_GREEN  = 1200;
	const DIFF_CYAN   = 1600;
	const DIFF_BLUE   = 2000;
	const DIFF_YELLOW = 2400;
	const DIFF_ORANGE = 2800;

	public static function get_atcoder_id($user_id)
	{
		$result = \DB::select('atcoder_users.id')
			->from('subscriptions')
			->join('atcoder_users', 'INNER')->on('subscriptions.atcoder_user_id', '=', 'atcoder_users.id')
			->where('subscriptions.user_id', '=', (int) $user_id)
			->execute()
			->as_array();
		if (empty($result)) return null;
		return (int) $result[0]['id'];
	}

	public static function get_stats_data($atcoder_id)
	{
		if ($atcoder_id === null) return array(); // 念の為
		$result = array();

		// 1. problemsの総数
		$result['total_problem_count'] = \DB::count_records('problems');

		// 2-5. 問題A~Gのそれぞれの総数とresult別の集計
		$result['by_letter'] = array();
		foreach (array('A', 'B', 'C', 'D', 'E', 'F', 'G') as $L) {
    		$result['by_letter'][$L] = array(
        		'total'  => 0,
        		'ac'     => 0,
        		'non_ac' => 0,
        		'no_sub' => 0,
    		);
		}
		$sub = \DB::select(
			'problem_id',
			\DB::expr("MAX(result = 'AC') AS has_ac")
		)
		->from('submissions')
		->where('atcoder_user_id', '=', (int) $atcoder_id)
		->group_by('problem_id');
		$letter_expr = "UPPER(RIGHT(p.problem_id, 1))"; // A, B, C, D, E, F, G
		$rows = array();
		$rows = \DB::select(
			\DB::expr($letter_expr . " AS letter"),
			\DB::expr("COUNT(*) AS total"), // 末尾が同じやつらを1グループにして，グループ内の数を数える
			\DB::expr("SUM(s.has_ac) AS ac"),
			\DB::expr("SUM(CASE WHEN s.has_ac IS NULL THEN 1 END) AS no_sub"),
		)
		->from(array('problems', 'p'))
		->join(array($sub, 's'), 'LEFT')->on('p.id', '=', 's.problem_id')
		->where('p.problem_id', 'REGEXP', '.*_[a-g]$') // 全件がヒットするはず
		->group_by(\DB::expr($letter_expr))
		->execute()
		->as_array();
		foreach ($rows as $row) {
			$letter = $row['letter']; // 'A'..'G'
			if (!isset($result['by_letter'][$letter])) continue;  // 念のため A〜G 以外は捨てる（REGEXPしてるので基本入らない）
		
			$result['by_letter'][$letter]['total']  = (int) $row['total'];
			$result['by_letter'][$letter]['ac']     = (int) $row['ac'];
			$result['by_letter'][$letter]['no_sub'] = (int) $row['no_sub'];
			$result['by_letter'][$letter]['non_ac'] = (int) $row['total'] - (int) $row['ac'] - (int) $row['no_sub'];
		}

		// 6-9. 難易度ごとの集計（色別）
		$result['by_difficulty'] = array();
		$sub = \DB::select(
			'problem_id', // problemsテーブルの内部id
			\DB::expr("MAX(result = 'AC') AS has_ac") // ACが1つでもあれば，1を返す 1つもなければ，0を返す
		)
		->from('submissions')
		->where('atcoder_user_id', '=', (int) $atcoder_id)
		->group_by('problem_id');
		$rows = \DB::select(
			array('p.difficulty', 'difficulty'), // difficultyの値ごとに集計する
			\DB::expr('COUNT(*) AS total'), // difficultyの値がかぶる問題が存在しなければこの値は1になるはずなので，基本1と考えてよい
			\DB::expr('SUM(s.has_ac) AS ac'), // acの数
			\DB::expr('SUM(CASE WHEN s.has_ac IS NULL THEN 1 END) AS no_sub'), // 未提出の数
		)
		->from(array('problems', 'p'))
		->join(array($sub, 's'), 'LEFT')->on('p.id', '=', 's.problem_id')
		->where('p.difficulty', 'IS NOT', null) // 難易度が不明の問題は統計の対象外
		->group_by('p.difficulty')
		->execute()
		->as_array();
		foreach ($rows as $row) {
			$color = self::get_color($row['difficulty']);
			if (!isset($result['by_difficulty'][$color])) $result['by_difficulty'][$color] = array('total' => 0, 'ac' => 0, 'no_sub' => 0, 'non_ac' => 0);
			
			$result['by_difficulty'][$color]['total'] += (int) $row['total'];
			$result['by_difficulty'][$color]['ac'] += (int) $row['ac'];
			$result['by_difficulty'][$color]['no_sub'] += (int) $row['no_sub'];
			$result['by_difficulty'][$color]['non_ac'] += (int) ($row['total'] - $row['ac'] - $row['no_sub']); // WAやTLEなど
		}
		
		// 10. 言語ごとのACした個数
		$ac_by_language = \DB::select(
			'language', 
			\DB::expr('COUNT(DISTINCT problem_id) as count')
		)
		->from('submissions')
		->where('atcoder_user_id', '=', (int) $atcoder_id)
		->where('result', '=', 'AC')
		->group_by('language')
		->execute()
		->as_array();
		$result['by_language'] = array();
		foreach ($ac_by_language as $row) $result['by_language'][$row['language']] = (int) $row['count']; // 言語ごとのACした個数
		arsort($result['by_language'], SORT_NUMERIC); // AC数で降順

		return $result;
	}

	private static function get_color($difficulty)
	{
		if ($difficulty < self::DIFF_GRAY) {
			return 'gray';
		} elseif ($difficulty < self::DIFF_BROWN) {
			return 'brown';
		} elseif ($difficulty < self::DIFF_GREEN) {
			return 'green';
		} elseif ($difficulty < self::DIFF_CYAN) {
			return 'cyan';
		} elseif ($difficulty < self::DIFF_BLUE) {
			return 'blue';
		} elseif ($difficulty < self::DIFF_YELLOW) {
			return 'yellow';
		} elseif ($difficulty < self::DIFF_ORANGE) {
			return 'orange';
		} else {
			return 'red';
		}
	}
}
