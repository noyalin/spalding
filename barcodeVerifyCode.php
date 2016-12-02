<?php
/**
 * Created by PhpStorm.
 * User: Bale
 * Date: 14-11-21
 * Time: 上午11:12
 */
session_start();
$verifyCode = strtolower(trim($_POST['verifyCode'])) ;
if($verifyCode != strtolower($_SESSION["helloweba_num_validcode"])){
    echo "failure";
}else{
	echo "success";
}
exit;