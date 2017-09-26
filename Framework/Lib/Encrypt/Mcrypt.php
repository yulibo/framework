<?php
namespace Core\Lib\Encrypt;

/**
 * Aes加密
 */
class Mcrypt
{
    /** 
     * 算法,另外还有192和256两种长度(mcrypt_list_algorithms();//mcrypt支持的加密算法列表).
     */
    const CIPHER = MCRYPT_RIJNDAEL_128;
    /**
     * 模式(mcrypt_list_modes();//mcrypt支持的加密模式列表).
     */
    const MODE = MCRYPT_MODE_ECB;
    
    private $aesKey = 'default';
    
    /**
     * 获取对象.
     * 
     * @return \Order\Module\class
     */
    public static function instance()
    {
        $class = get_called_class();
        return new $class;
    }

    public function setKey($key){
        $this->aesKey = $key;
    }    
    /**
     * AES加密.
     * 
     * @param string $input 待加密字符串.
     * 
     * @return string
     */
    public function aesEncrypt($input)
    {
        // 解决PHP与JAVA的AES加密不一致算法.
        $blocksize = mcrypt_get_block_size(self::CIPHER, self::MODE);
        $pad = $blocksize - (strlen($input) % $blocksize);
        $input .= str_repeat(chr($pad), $pad);
        
        $td = mcrypt_module_open(self::CIPHER, '', self::MODE, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_module_close($td);
        $data = mcrypt_encrypt(self::CIPHER, base64_decode($this->aesKey), $input, self::MODE, $iv);
        return base64_encode($data);
    }

    /**
     * AES解密.
     * 
     * @param string $str 待解密字符串.
     * 
     * @return string
     */
    public function aesDecrypt($str)
    {
        $decrypted = mcrypt_decrypt(self::CIPHER, base64_decode($this->aesKey), base64_decode($str), self::MODE);
        $decs = strlen($decrypted);
        $padding = ord($decrypted[$decs - 1]);
        return substr($decrypted, 0, -$padding);
    }

}
