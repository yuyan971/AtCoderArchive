<?php
namespace Stats;
use Stats\Model_Db;

class Controller_Stats extends \Controller
{
	public function before()
	{
		if (!\Auth::check()) return \Response::redirect('/auth/login');
	}
	
	public function action_index()
	{
		list(, $user_id) = \Auth::get_user_id();
		$atcoder_id = Model_Db::get_atcoder_id($user_id);
		$stats_data = Model_Db::get_stats_data($atcoder_id); // DBから統計表示用のデータを取得

		$view = array();
		$view['header'] = \View::forge('myapp/header');
		$view['content'] = \View::forge('content', array('stats_data' => $stats_data));
		return \Response::forge(\View::forge('myapp/base', $view));
	}
}