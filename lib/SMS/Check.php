<?php
/**
 *
 */
final class SMS_Check
{
    /**
     * 取得某个用户某次活动的手机验证码
     * @param $uin 用户ID 小于10000系统保留
     * @param $actId 活动ID  小于1000系统保留
     * @param $telephone 用户手机号
     * @return bool|int 4位数的验证码
     */
    public static function  getTelephoneCode($uin, $actId, $telephone)
    {
        if (empty($uin) || empty($actId) || empty($telephone)) {
            return false;
        }

        $time = time();

        $timeFeature = hexdec(substr(md5($time), 0, 3)) & 0x1F1;

        $telephoneFeature = hexdec(substr(md5($telephone), 8, 4));

        $actIdFeature = hexdec(substr(md5($actId), 16, 4));

        $uinFeature = hexdec(substr(md5($uin), 24, 4));

        $sumFeature = $telephoneFeature + $actIdFeature + $uinFeature;

        $sumFeature = $sumFeature % 10000;

        if ($sumFeature < 1000) {
            $sumFeature = 7878;
        }

        $result = $sumFeature | $timeFeature;

        Mage::log("result = ".$result."----sumFeature = ".$sumFeature."----timeFeature".$timeFeature);
        return $result;
    }


    /**
     * 验证用户的手机验证码
     * @param $uin 用户ID 小于10000系统保留
     * @param $actId 活动ID  小于1000系统保留
     * @param $telephone 用户手机号
     * @param $code getTelephoneCode生成的验证码
     * @return bool 是否正确
     */
    public static function  checkTelephoneCode($uin, $actId, $telephone, $code)
    {

        if (empty($uin) || empty($actId) || empty($telephone) || empty($code)) {
            return false;
        }

        $telephoneFeature = hexdec(substr(md5($telephone), 8, 4));

        $actIdFeature = hexdec(substr(md5($actId), 16, 4));

        $uinFeature = hexdec(substr(md5($uin), 24, 4));

        $sumFeature = $telephoneFeature + $actIdFeature + $uinFeature;

        $sumFeature = $sumFeature % 10000;

        if ($sumFeature < 1000) {
            $sumFeature = 7878;
        }

        $sumFeature = $sumFeature & 0xE0E;

        $code = $code & 0xE0E;

        if ($sumFeature == $code) {
            return true;
        }
        return false;
    }
}