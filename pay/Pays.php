<?php include 'config.php';?>
<?php
//回调地址
$orderNo=$_POST['orderNo'];
$cardType=$_POST['cardType'];
$bankCardNum=$_POST['bankCardNum'];
$bankCardPhone=$_POST['bankCardPhone'];
$bankCode=$_POST['bankCode'];
$cardUserName=$_POST['cardUserName'];
$idCardNum=$_POST['idCardNum'];
$verifyCode=$_POST['verifyCode'];
$PostUrl="bankCardNum=".$bankCardNum."&bankCardPhone=".$bankCardPhone."&cardType=".$cardType."&cardUserName=".$cardUserName."&idCardNum=".$idCardNum."&orderNo=".$orderNo."&verifyCode=".$verifyCode;
$res = openssl_pkey_get_private($private_key);

$sign=rsaSign($PostUrl,$res);
//$data=HttpPost("http://47.92.118.177/api/apiPay/confirmPay",array('sign'=>$sign,'orderNo'=>$orderNo,'cardType'=>$cardType,'bankCardNum'=>$bankCardNum,'bankCardPhone'=>$bankCardPhone,'cardUserName'=>$cardUserName,'idCardNum'=>$idCardNum,'verifyCode'=>$verifyCode));
$Purl='sign='.urlencode($sign).'&orderNo='.$orderNo.'&cardType='.$cardType.'&bankCardNum='.$bankCardNum.'&bankCardPhone='.$bankCardPhone.'&cardUserName='.$cardUserName.'&idCardNum='.$idCardNum.'&verifyCode='.$verifyCode;
$data=HttpPost("http://47.92.118.177/api/apiPay/confirmPay",$Purl);
echo($data);
?>
<?php
$pkeyid = openssl_get_publickey($public_key);
$Yzsign=json_decode($data)->sign;
$NewUrl="";
$Url="";
$arr = json_decode($data);
foreach ($arr as $val=>$value )
{
	if($val!="sign")
	{
		$Key=$val.'='.$value;
		$Url=$Url.$Key.'&';
	}
}
$arr = explode("&", $Url);
natcasesort($arr);
foreach ($arr as $val )
{
	$NewUrl=$NewUrl.$val.'&';
}
$NewUrl=substr($NewUrl,1,strlen($NewUrl)-2);
if(rsaVerify($NewUrl,$Yzsign,$pkeyid)){
        $h_money = number_format($_GET['orderAmount']/100, 2, ".", "");
        $orderid = $_GET['orderNo'];
        $sql = "select * from `blypay_order` where `orderid`='" . $orderid . "'  and `amout`='" . $h_money . "' and `state`='0' LIMIT 1";
        $rs = $db->get_one($sql);
        if ($rs) {
            $h_bank = $rs["bank"];
            $h_userName = $rs["username"];
            $sql = "insert into h_recharge (h_userName,h_money,h_bank,h_addTime,h_state,h_actIP,h_isReturn) values ('" . $h_userName . "','" . $h_money . "','" . $h_bank . "','" . date('Y-m-d H:i:s') . "','1','" . $_SERVER["REMOTE_ADDR"] . "','1')";
            $result = $db->query($sql);
            if ($result) {
            }
            $sql = "update `h_member` set ";
            $sql .= "h_point2 = h_point2 + {$h_money} ";
            $sql .= "where h_userName = '" . $h_userName . "' ";
            $db->query($sql);
            $h_reply = "在线支付";
            $sql = "insert into `h_log_point2` set ";
            $sql .= "h_userName = '" . $h_userName . "', ";
            $sql .= "h_price = '" . $h_money . "', ";
            $sql .= "h_type = '充值', ";
            $sql .= "h_about = '{$h_reply}', ";
            $sql .= "h_addTime = '" . date('Y-m-d H:i:s') . "', ";
            $sql .= "h_actIP = '" . $_SERVER["REMOTE_ADDR"] . "' ";
            $db->query($sql);
            $sql = "update `blypay_order` set state='1' where `orderid`='" . $orderid . "'  and `amout`='" . $h_money . "' and `state`='0'";
            $db->query($sql);
        } else {
        }
        echo "充值成功<br />订单号：" . $out_trade_no . "<br />金额:" . $money . "元";
		exit('<script type="text/javascript" language="javascript">
 window.setTimeout(function(){
	 window.location.href="/member/"; 
 },3000);
</script> '); 
}else{
	echo("支付失败!");
}
?>