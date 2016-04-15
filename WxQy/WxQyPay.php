<?php
/**
 * Created by PhpStorm.
 * User: 单线程
 * Date: 2016/1/4
 * Time: 15:58
 */

namespace Tool\WxQy;
use Tool\DB;
class WxQyPay extends WxQYUtil
{
    public function unifiedOrder($data)
    {
        $data['appid']=config("myapp.corpId");
        $data['mch_id']=config("myapp.mchId");
        $data['spbill_create_ip']=$_SERVER['REMOTE_ADDR'];
        $data['nonce_str']=$this->createRandStr();
        $data['sign']=$this->getSign($data);
        $xml=$this->arrayToXml($data);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $res=$this->payCurl($xml,$url);
        $data=$this->xmlToArray($res);
        return $data;
    }
    public function getJsApiParameters($order)
    {
        if (!array_key_exists("appid", $order)
            || !array_key_exists("prepay_id", $order)
            || $order['prepay_id'] == ""
        ) {
            return false;
        }
        $data=[];
        $timeStamp = time();
        $data['appId']=$order['appid'];
        $data['timeStamp']="$timeStamp";
        $data['nonceStr']=$this->createRandStr();
        $data['package']="prepay_id=" . $order['prepay_id'];
        $data['signType']="MD5";
        $data['paySign']=$this->getSign($data);
        return $data;
    }
    public function notify()
    {
        $res = isset($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : file_get_contents('php://input', 'r');
        $data = $this->xmlToArray($res);
        $arr = [];
        $sign = $this->checkSign($data);
        if ($sign == FALSE) {
            $arr['return_code'] = 'FAIL';//返回状态码
            $arr['return_msg'] = '签名失败';//返回信息
        } else {
            $arr['return_code'] = 'SUCCESS';//设置返回码
        }
        $returnXml = $this->arrayToXml($arr);
        echo $returnXml;
        $data['status']='FAIL';
        if ($sign == TRUE) {
            if ($data["return_code"] == "FAIL") {
                $data['type'] = '【通信出错】';
            } elseif ($data["result_code"] == "FAIL") {
                $data['type'] = '【业务出错】';
            } else {
                $data['type'] = '【支付成功】';
                $data['status']='SUCCESS';
            }
        } else {
            exit;
        }
        return $data;
    }
    public function refund($data)
    {
        $data['out_refund_no'] = uniqid('refund-');
        $data['op_user_id'] = config("myapp.mchId");
        $data['appid'] = config("myapp.corpId");
        $data['mch_id']=config("myapp.mchId");
        $data['nonce_str']=$this->createRandStr();
        $data['sign']=$this->getSign($data);
        $xml=$this->arrayToXml($data);
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $res=$this->payCurl($xml,$url);
        $data=$this->xmlToArray($res);
        return $data;
    }
}