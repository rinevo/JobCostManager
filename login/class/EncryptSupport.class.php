<?php
class EncryptSupport {

	/**
	 * 暗号化キー
	 * @var unknown_type
	 */
	protected $key = 'NyTv0y24TPxQQOcFjuvCwjdRDrHJqLO71io6oobZGIMTa6fBtH';
	protected $td;
	protected $iv;
	protected $ks;

	/**
	 * コンストラクタ
	 * @param unknown_type $key
	 */
	function __construct($key = null) {

		// 暗号モジュールをオープン
		$this->td = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_ECB, '');

		// 初期化ベクトルを用意
		// 5.3より前のWindowsでは、MCRYPT_DEV_URANDOM の代わりに MCRYPT_RAND を使用する
		$this->iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->td), MCRYPT_DEV_URANDOM);
		$this->ks = mcrypt_enc_get_key_size($this->td);

		// キーを作成
		if ($key) {
			$this->key = substr(md5($key), 0, $this->ks);
		} else {
			$this->key = substr(md5($this->key), 0, $this->ks);
		}
	}

	/**
	 * デストラクタ
	 */
	function __destruct() {
		// 暗号モジュールをクローズ
		mcrypt_module_close($this->td);
	}

	/**
	 * 暗号化
	 * @param unknown_type $string
	 * @return string
	 */
	function EncodeString($string) {
		if (strlen($string) < 1) {
			return '';
		}

		$base64_data = base64_encode($string);

		mcrypt_generic_init($this->td, $this->key, $this->iv);				// 暗号化処理を初期化
		$encrypted_data = mcrypt_generic($this->td, $base64_data);			// データを暗号化
		mcrypt_generic_deinit($this->td);									// 暗号化ハンドラを終了

		return base64_encode($encrypted_data);
	}

	/**
	 * 複号
	 * @param unknown_type $string
	 * @return string
	 */
	function DecodeString($string) {
		if (strlen($string) < 1) {
			return '';
		}

		$encrypted_data = base64_decode($string);

		mcrypt_generic_init($this->td, $this->key, $this->iv);				// 暗号化処理を初期化
		$decrypted_data = mdecrypt_generic($this->td, $encrypted_data);		// データを複号
		mcrypt_generic_deinit($this->td);									// 暗号化ハンドラを終了

		return base64_decode($decrypted_data);
	}
}
