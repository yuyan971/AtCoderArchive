<?php
namespace Auth; // モジュール名


class Model_Db extends \Model
{
	public static function get_atcoder_username($user_id)
	{
		$result = \DB::select('atcoder_users.user_name')
			->from('subscriptions')
			->join('atcoder_users', 'INNER')->on('subscriptions.atcoder_user_id', '=', 'atcoder_users.id')
			->where('subscriptions.user_id', '=', (int) $user_id)
			->execute()
			->as_array();
		
		if (empty($result)) {
			return null;
		}
		return $result[0]['user_name'];
	}

	public static function update_user_data($user_id, $new_email, $new_atcoder_username)
	{
		$user_id = (int) $user_id;
		$new_email = trim($new_email);
		$new_atcoder_username = trim($new_atcoder_username);

		// 同一メールが他ユーザに既に存在する場合は例外
		$existing = \DB::select('id')
			->from('users')
			->where('email', '=', $new_email)
			->where('id', '!=', $user_id)
			->execute()->as_array();
		if (!empty($existing)) {
			throw new \Exception('This email is already in use by another account.');
		}

		\DB::update('users')
			->set(array('email' => $new_email, 'username' => $new_email))
			->where('id', '=', $user_id)
			->execute();

		// AtCoder購読を更新（既存ロジックを流用）
		static::subscribe_atcoder_user($user_id, $new_atcoder_username);
	}

	public static function subscribe_atcoder_user($user_id, $atcoder_username)
	{
		$user_id = (int) $user_id;
		$atcoder_username = trim($atcoder_username);

		// 既存の購読情報を取得
		$existing_subscription = \DB::select('subscriptions.id', 'subscriptions.atcoder_user_id')
			->from('subscriptions')
			->where('subscriptions.user_id', '=', $user_id)
			->execute()
			->as_array();

		$atcoder_user = \DB::select('id')
			->from('atcoder_users')
			->where('user_name', '=', $atcoder_username)
			->execute()
			->as_array();

		if (empty($atcoder_user)) { // DBに存在しないAtCoderユーザーの場合は登録
			$result = \DB::insert('atcoder_users')
				->set(array('user_name' => $atcoder_username))
				->execute();
			$atcoder_user_id = $result[0];
		} else {
			$atcoder_user_id = $atcoder_user[0]['id']; // これはありうる．
		}

		// 購読情報を更新または作成
		if (!empty($existing_subscription)) { // 既存の購読がある場合は更新 << 新規登録でそんなことが起こるはずない
			\DB::update('subscriptions')
				->set(array('atcoder_user_id' => $atcoder_user_id))
				->where('user_id', '=', $user_id)
				->execute();
		} else {
			\DB::insert('subscriptions')
				->set(array(
					'user_id' => $user_id,
					'atcoder_user_id' => $atcoder_user_id,
				))
				->execute();
		}
	}

	public static function logical_delete_user($user_id) // 論理削除のユーザ退会
	{
		$user_id = (int) $user_id;

		$user = \DB::select('id', 'email')
			->from('users')
			->where('id', '=', $user_id)
			->execute()
			->current();

		if ($user === null) throw new \Exception('User not found.'); // 存在しない訳がないが念のため

		$now = time(); // エポック秒
		\DB::update('users')
			->set(array(
				'username'   => '',
				'email'      => '',
				'remove_email' => $user['email'],
				'remove_key' => $user_id,
				'deleted_at' => $now,
			))
			->where('id', '=', $user_id)
			->execute();
	}
}
