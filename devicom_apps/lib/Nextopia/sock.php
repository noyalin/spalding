<?php
//converts query(mike=34&chris=11) string to array[var]=val
function f_param2array($str){
 $str=preg_replace("/^\?/i","",$str);
 $t=split("\&",$str);
 $ret=array();
 foreach($t as $v){
  if($v!=""){
   $t2=split("=",$v,2);
   $ret[$t2[0]]=$t2[1];
  }
 }
 return $ret;
}

//vget and vpost can be string (mike=1&zoo=2) or array[key]=val
//vcookie must be array[key]=val
//vurl can have GET params in it (?mike=1&m=4)
//returns an array with fileds: error'=>'','result'=>'','cookies'=>''
function f_socket($vurl,$vget='',$vpost='',$vcookie='',$vtimeout=15){
 global $sock_count;
 $sock_count++;
 $vurl=preg_replace("/^https?:\/\//i","",$vurl);
 $retval=array('error'=>'','result'=>'','cookies'=>'');

 if(is_array($vpost) || $vpost !=''){
 $method='POST';
 }else{
 $method='GET';
 }
 
 if(!is_array($vget)){
  if($vget==""){
   $vget=array();
  }else{
   $vget=f_param2array($vget);
  }
 }
 if(!is_array($vpost)){
  if($vpost==""){
   $vpost=array();
  }else{
   $vpost=f_param2array($vpost);
  }
 }

 $turl=split("/",$vurl,2);
 $domain=$turl[0];
 $path="/".$turl[1];

 
 $turl=split("\?",$path,2);
 $path=$turl[0];
 
 //add parameters
 if($turl[1]!=""){
  $vget=array_merge($vget, f_param2array($turl[1]));
 }
 
 foreach($vget as $k=>$v){
  $params.='&'. $k .'='. $v;
 }
 if($params!=""){
  $params=substr($params,1);
 }

 foreach($vpost as $k=>$v){
  $post.='&'. $k .'='. $v;
 }
 if($post!=""){
  $post=substr($post,1) . "&x=0&y=0";
 }

 if($vcookie!=""){
  foreach($vcookie as $k=>$v){
   $cookie.=' '.$k .'='. $v.';';
  }
  if($cookie!=""){
   $cookie='Cookie:'.$cookie."\r\n";
  }
 }
 $data="$method " . $path . (($params=="")?"":"?".$params) . " HTTP/1.0\r\n";
 $data.="Content-Type: application/x-www-form-urlencoded\r\n";
 $data.="User-Agent: Mozilla/4.0 (compatible; MSIE 4.0; Windows NT 4.0; SYMPA)\r\n";
 $data.="Host: $domain\r\n";

 $data.=$cookie;
 $data.="Content-Length: " . strlen($post) . "\r\n\r\n";
 $data.=$post;

 $socket = fsockopen($domain, 80, $errno, $errstr, $vtimeout);
 if (!$socket) {
  //echo "ERROR: $domain\n\n";
  //exit;
  $retval['result'] = "Error";
  $retval['error']="$errstr ($errno)";
  return $retval;
 }
//echo "\n\n$data\n\n";
 fwrite($socket, $data);
 $socket_data="";
 while (!feof($socket)) {
  $socket_data .= fgets($socket, 128);
 }
 fclose($socket);
 
 //read in cookies
 $old_p_s=0;
 $ret_cookies=array();
 
 while(1){
  $p_s=strpos($socket_data,'Set-Cookie:',$p_s);
  if($p_s> $old_p_s){
   $old_p_s=$p_s;
   $p_e=strpos($socket_data,';',$p_s);
   $t=trim(substr($socket_data,$p_s+11,$p_e-$p_s-11));
   $p_s=$p_e;
   $t2=split("=",$t,2);
   $ret_cookies[$t2[0]]=$t2[1];
  }else{
   break;
  }
 }
  if($vcookie!=""){
   foreach($vcookie as $k =>$v){
    $retval['cookies'][$k]=$v;
   }
  }
  foreach($ret_cookies as $k =>$v){
   $retval['cookies'][$k]=$v;
  }

 //$retval['cookies']=$ret_cookies;
 $retval['result']=$socket_data;

 if(ereg(" 302 ",substr($socket_data,0,100))){
  $ret=array();
  preg_match("/location: ([^\n]*)\n/ims",$socket_data,$ret);
//print_r($ret);
  if(ereg("http://",$ret[1])){
   
  }elseif(ereg("^\/",$ret[1])){
   $ret[1]='http://'.$domain . $ret[1];
   //echo '1 http://'.$domain . $ret[1];
  }else{
   $ret2=array();
   preg_match("/(.*?)[^\/]*?$/ims",$vurl,$ret2);
   $ret[1]=$ret2[1] . $ret[1]; 
   //echo $ret[1] . ' ' . $vurl;

  }  
  return f_socket($ret[1],'','',$retval['cookies'],$vtimeout);
 }

 return $retval;
}
