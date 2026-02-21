<?php
namespace Auth;
use Auth\Model_Db;

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

    public function post_login()
    {
        if (!\Security::check_token()) return \Response::redirect('/auth/login'); // CSRF対策
        $email = trim(\Input::post('email', ''));
        $password = \Input::post('password', '');
        $errors = array();

        if ($email === '' || $password === '') $errors[] = 'Email and password are required.';
        if (\Auth::login($email, $password)) return \Response::redirect('/home');
        if (empty($errors)) $errors[] = 'Invalid email or password.';

        \Session::set_flash('login_errors', $errors);
        \Session::set_flash('login_email', $email);
        return \Response::redirect('/auth/login');
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


    public function post_signup()
	{
		if (!\Security::check_token()) return \Response::redirect('/auth/signup'); // CSRF対策
		$errors = array();
		$atcoder_username = trim(\Input::post('atcoder_username', ''));
		$email = trim(\Input::post('email', ''));
		$password = \Input::post('password', '');
		$confirm = \Input::post('confirm_password', '');
		if ($atcoder_username === '') $errors[] = 'AtCoder Username is required.';
		if ($email === '') $errors[] = 'Email is required.';
		if ($password === '') $errors[] = 'Password is required.';
		if ($password !== $confirm) $errors[] = 'Password and confirmation do not match.';
		if (empty($errors)) {
			try {
				$user_id = \Auth::create_user($email, $password, $email, 1);
				Model_Db::subscribe_atcoder_user($user_id, $atcoder_username);
				\Auth::login($email, $password);
				return \Response::redirect('/home');
			} catch (\SimpleUserUpdateException $e) {
				$errors[] = $e->getMessage();
			} catch (\Exception $e) {
				$errors[] = 'Registration failed. Please try again.';
			}
		}
		\Session::set_flash('signup_errors', $errors);
		\Session::set_flash('signup_email', $email);
		\Session::set_flash('signup_atcoder_username', $atcoder_username);
		return \Response::redirect('/auth/signup');
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

    public function post_update() // auth/settingsより実行
	{
		if (!\Security::check_token()) return \Response::redirect('/auth/settings'); // CSRF対策
		$errors = array();
		$new_email = trim(\Input::post('email', ''));
		$new_atcoder_username = trim(\Input::post('atcoder_username', ''));
		$current_password = \Input::post('current_password', '');
		$new_password = \Input::post('new_password', '');
		$confirm_password = \Input::post('confirm_password', '');
		if ($new_email === '') $errors[] = 'Email is required.';
		if ($new_atcoder_username === '') $errors[] = 'AtCoder Username is required.';
		if ($current_password === '') $errors[] = 'Current password is required.';

		if (!($new_password === '' && $confirm_password === '')) { // パスワード変更の意思がないなら，スルー
			if ($new_password === '') $errors[] = 'New password is required.';
			if (($new_password !== '') && ($new_password !== $confirm_password)) $errors[] = 'New password and confirmation do not match.';
			try {
				\Auth::change_password($current_password, $new_password); // 第3引数のuser_nameがnullのときは，どんな処理が行われているのか？
				$current_password = $new_password;
			} catch (\SimpleUserUpdateException $e) {
				$errors[] = $e->getMessage();
			}
		}
		if (!empty($errors)) {
			\Session::set_flash('settings_update_errors', $errors);
			\Session::set_flash('settings_update_success', false);
			return \Response::redirect('/auth/settings');
		}
		// 更新処理に入る
		try {
			list(, $user_id) = \Auth::get_user_id();
			Model_Db::update_user_data($user_id, $new_email, $new_atcoder_username); // メールアドレスとユーザ名とAtCoderユーザ名を同時に更新する　(Auth::update_userを使えないので，自前で更新) << ユーザ名変更不可なため
			\Auth::login($new_email, $current_password); // DBを更新するとログアウトされてしまうっぽいので，その対策
			\Session::set_flash('settings_update_success', true);
			return \Response::redirect('/auth/settings');
		} catch (\Exception $e) {
			$errors[] = $e->getMessage();
			\Session::set_flash('settings_update_success', false);
		}
		\Session::set_flash('settings_update_errors', $errors);
		return \Response::redirect('/auth/settings');
	}

	public function post_logout()
	{
		if (!\Security::check_token()) return \Response::redirect('/auth/settings'); // CSRF対策
		\Auth::logout();
		return \Response::redirect('/auth/login');
	}

	public function post_leave()
	{
		if (!\Security::check_token()) return \Response::redirect('/auth/settings'); // CSRF対策
		try {
			list(, $user_id) = \Auth::get_user_id();
			Model_Db::logical_delete_user($user_id);
			\Auth::logout();
		} catch (\Exception $e) {
			\Session::set_flash('leave_errors', array($e->getMessage()));
			return \Response::redirect('/auth/settings');
		}
		return \Response::redirect('/auth/login');
	}
}