<?php
class Devicom_Weixindev_Model_Dbconn extends Mage_Core_Model_Abstract{
    public $readConnection;
    public $writeConnection;
    public function  __construct(){
        $resource = Mage::getSingleton('core/resource');
        $this->readConnection = $resource->getConnection('core_read');
        $this->writeConnection = $resource->getConnection('core_write');
        parent :: __construct();
    }
    public function updateUserInfo($openid,$phone=null,$orderid=null){

        $res = $this->readConnection->fetchRow("select id  FROM weixin_user where openid='$openid'");
        if(empty($res)){
            $time = time();
            $status = 1;
            $sql = "insert into weixin_user (openid,createtime,status) values ('$openid',$time,$status)";
            $this->writeConnection->query($sql);
        }
        //更新手机信息
        $time = time();
        $sqlUpdate = "update weixin_user set phone='$phone',orderid='$orderid',createtime=$time where openid='$openid'";
        $this->writeConnection->query($sqlUpdate);
    }

    /**
     * @param $token
     * 当用户关注时，存用户OPENID
     */
    public function saveOpenID($openid){
        $res = $this->readConnection->fetchRow("select id  FROM weixin_user where openid='$openid'");
        if(empty($res)){
            $time = time();
            $status = 1;
            $sql = "insert into weixin_user (openid,createtime,status) values ('$openid',$time,$status)";
            $this->writeConnection->query($sql);
        }
    }
//存储用户手机号
    public function savePhone($openid,$phonenum){
        $sql = "update weixin_user set phone='$phonenum' where openid='$openid'";
        $this->writeConnection->query($sql);
    }
//存储用户订单号
    public function saveOrdernum($openid,$ordernum){
        $sql = "update weixin_user set orderid='$ordernum' where openid='$openid'";
        $this->writeConnection->query($sql);
    }

    public function getOneOpenidRecord($openid){
        $res = $this->readConnection->fetchRow("select id  FROM weixin_user where openid='$openid'");
        return $res;
    }
    public function getRes(){
        $res = $this->readConnection->fetchRow("select id  FROM weixin_user");
        return $res;
    }

    public function saveSessionLast($last,$openid){
        $sql = "update weixin_user set sessionlast='$last' where openid='$openid'";
        $this->writeConnection->query($sql);
    }

    public function getSessionLast($openid){
        $res = $this->readConnection->fetchRow("select sessionlast  FROM weixin_user where openid='$openid'");
        return $res['sessionlast'];
    }

    public function getBindPhone($openid){
        $res = $this->readConnection->fetchRow("select phone  FROM weixin_user where openid='$openid'");
        return $res['phone'];
    }

    /**
     * 判断用户是否已经有收件人手机号
     */
    public function getPhoneFromWeixinIdentity($openid){
        $res = $this->readConnection->fetchRow("select phone  FROM weixin_user where openid='$openid'");
        return $res['phone'];
    }

    /**
     * 插入表WeixinIdentity
     */
    public function insertPhoneAndIdentity($openid,$username,$phone,$idcard){
        $res = $this->readConnection->fetchRow("select id  FROM weixin_user where openid='$openid'");
        $id = $res['id'];
        //是否存在
        $resIdentity =$this->readConnection->fetchRow("select id  FROM weixin_identity where uid=$id and phone='$phone' and  username='$username'");
        if($resIdentity && isset($resIdentity['id'])){
            //此记录存在，不需要插入
            //更新表
            $sql = "update weixin_identity set identity_number=? where id=?";
            $this->writeConnection->query($sql,array($idcard,$resIdentity['id']));
            return $resIdentity['id'];
        }else{
            $sql = "insert into `weixin_identity` (uid,phone,username,identity_number) values($id,'$phone','$username','$idcard')";
            $this->writeConnection->query($sql);
            return $this->getId();
        }

    }

    public function updateIdentity($id,$identity){
        $sql = "update weixin_identity set identity_number='$identity' where id=$id";
        $this->writeConnection->query($sql);
    }
    public function saveSessionIdentityNum($openid,$str){
        $sql = "update weixin_user set session_identity_num='$str' where openid='$openid'";
        $id = $this->writeConnection->query($sql);
        return $id.$sql;
    }

    public function getSessionIdentityNum($openid){
        $res = $this->readConnection->fetchRow("select session_identity_num  FROM weixin_user where openid='$openid'");
        return json_decode($res['session_identity_num'],true);
    }

    public function getIdentityInfoById($id){
        $res = $this->readConnection->fetchRow("select phone,username  FROM weixin_identity where id=$id");
        return $res;
    }

    public function saveSignUpInfo($openid, $slogan, $city, $username, $telephone){
        $count = $this->getSignUpInfo($openid);
        if ($count > 0) {
            return false;
        }
        $date = date('Y-m-d H:i:s', time());
        $sql = "insert into weixin_nba(openid,username,telephone,city,slogan,join_time) values('" . $openid . "','" . $username
            . "','" . $telephone . "','" . $city . "','" . $slogan . "','" . $date . "')";
        $this->writeConnection->query($sql);
        return true;
    }

    public function getSignUpInfo($openid){
        $sql = "select count(1) from weixin_nba where openid = '" . $openid . "'";
        return $this->readConnection->fetchOne($sql);
    }
}