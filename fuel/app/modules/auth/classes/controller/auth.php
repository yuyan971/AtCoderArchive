<?php
namespace Auth;

class Controller_Auth extends \Controller
{
	/** ログインチェックを行わないアクション */
	const PUBLIC_ACTIONS = array('login', 'signup'); // 無限リダイレクトを防ぐため

	public function before()
	{
		$request = \Request::active();
		if ($request && in_array($request->action, static::PUBLIC_ACTIONS, true)) return;
		if (!\Auth::check()) return \Response::redirect('/auth/login');
	}

	private function create_login_signup_view_vars($form_type) // 引数にloginかsignupが来ることしか想定していない．
	{
		$is_login = ($form_type === 'login');
		return array(
			'is_login'      => $is_login,
			'title'         => $is_login ? 'Log in' : 'Sign up',
			'action'        => $is_login ? '/auth/login' : '/auth/signup',
			'submit_label'  => $is_login ? 'Log in' : 'Sign up',
			'switch_url'    => $is_login ? '/auth/signup' : '/auth/login',
			'switch_label'  => $is_login ? 'Create an account' : 'Already have an account? Log in',
		);
	}

	public function get_login()
	{
		$errors = (array) \Session::get_flash('login_errors', array());
		$email = (string) \Session::get_flash('login_email', '');
		$view = array();
		$view['header'] = \View::forge('myapp/header');
		$view['content'] = \View::forge('login_signup', array_merge(
			$this->create_login_signup_view_vars('login'),
			array('errors' => $errors, 'email' => $email)
		));
		return \Response::forge(\View::forge('myapp/base', $view));
	}


	public function get_signup()
	{
		$errors = (array) \Session::get_flash('signup_errors', array());
		$email = (string) \Session::get_flash('signup_email', '');
		$atcoder_username = (string) \Session::get_flash('signup_atcoder_username', '');
		$view = array();
		$view['header'] = \View::forge('myapp/header');
		$view['content'] = \View::forge('login_signup', array_merge(
			$this->create_login_signup_view_vars('signup'),
			array('errors' => $errors, 'email' => $email, 'atcoder_username' => $atcoder_username)
		));
		return \Response::forge(\View::forge('myapp/base', $view));
	}

	public function get_settings()
	{
		$leave_errors = (array) \Session::get_flash('leave_errors', array()); // 退会時のエラー
		$settings_update_errors = (array) \Session::get_flash('settings_update_errors', array()); // 設定更新時のエラー
		$errors = empty($leave_errors) ? $settings_update_errors : $leave_errors; // 同時にエラーが発生する可能性はない．

		$success = (bool) \Session::get_flash('settings_update_success', false);

		$user_id = null;
		list(, $user_id) = \Auth::get_user_id();
		$current_email = \Auth::get_email();
		$current_atcoder_username = Model_Db::get_atcoder_username($user_id);

		$view = array();
		$view['header'] = \View::forge('myapp/header');
		$view['content'] = \View::forge('settings', array(
			'email' => $current_email,
			'atcoder_username' => $current_atcoder_username,
			'errors' => $errors,
			'success' => $success,
		));
		return \Response::forge(\View::forge('myapp/base', $view));
	}
}