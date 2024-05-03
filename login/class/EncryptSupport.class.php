<?php
class EncryptSupport {

	CONST OPENSSL_CIPHER_ALGO = 'aes-256-cbc';
	CONST OPENSSL_OPTIONS = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;

	/**
	 * 暗号化キー
	 * @var unknown_type
	 */
	protected $key = 'NyTv0y24TPxQQOcFjuvCwjdRDrHJqLO71io6oobZGIMTa6fBtH';
	protected $iv = null;

	/**
	 * コンストラクタ
	 * @param unknown_type $key
	 */
	public function __construct($key = null) {

		// 初期化ベクトルを用意
		$iv_length = openssl_cipher_iv_length(EncryptSupport::OPENSSL_CIPHER_ALGO);
		$this->iv = bin2hex(openssl_random_pseudo_bytes($iv_length));

		// キーを作成
		if ($key) {
			$this->key = md5($key);
		} else {
			$this->key = md5($this->key);
		}
	}

	/**
	 * 暗号化
	 * @param unknown_type $string
	 * @return string
	 */
	public function EncodeString($string) {
		if (strlen($string) < 1) {
			return '';
		}

		return $this->encrypt($string);
	}

	/**
	 * 複号
	 * @param unknown_type $string
	 * @return string
	 */
	public function DecodeString($string) {
		if (strlen($string) < 1) {
			return '';
		}

		return $this->decrypt($string);
	}

    private function encrypt($plain_text) {
        return bin2hex(
            openssl_encrypt(
                $this->pkcs5_padding($plain_text),
                EncryptSupport::OPENSSL_CIPHER_ALGO,
                $this->key,
                EncryptSupport::OPENSSL_OPTIONS,
                hex2bin($this->iv)
            )
        );
    }

    private function decrypt($encrypted_text) {
        return $this->pkcs5_suppress(
            openssl_decrypt(
                hex2bin($encrypted_text),
                EncryptSupport::OPENSSL_CIPHER_ALGO,
                $this->key,
                EncryptSupport::OPENSSL_OPTIONS,
                hex2bin($this->iv)
            )
        );
    }

    private function pkcs5_padding($data) {
        $block_size = openssl_cipher_iv_length(EncryptSupport::OPENSSL_CIPHER_ALGO);
        $padding = $block_size - (strlen($data) % $block_size);
        return $data . str_repeat(chr($padding), $padding);
    }

    private function pkcs5_suppress($data) {
        $padding = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padding);
    }
}
