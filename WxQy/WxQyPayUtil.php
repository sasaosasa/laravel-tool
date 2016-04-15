<?php
/**
 * Created by PhpStorm.
 * User: 单线程
 * Date: 2016/3/17
 * Time: 0:00
 */

namespace Tool\WxQy;


class WxQyPayUtil extends WxQyUtil
{
    public $mchId;

    public function __construct()
    {
        $mchId = config('myapp.mchId');
        if (empty($mchId)) {
            _pack("缺少mchId配置！", false);
        }
        $this->mchId = config('myapp.mchId');
        parent::__construct();
    }

    /**
     * @return string 商户订单号 商户订单号（每个订单号必须唯一）
     * 组成：mch_id+yyyymmdd+10位一天内不能重复的数字。
     */
    public function mchBillNo()
    {
        return $this->mchId . date("Ymd") . $this->createRandNumber();
    }

    /**
     * userid转换成openid接口
     */
    public function getOpenid($user_id,$agent_id)
    {
        $data['userid']=$user_id;
        $data['agentid']=$agent_id;
        $url="https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=ACCESS_TOKEN";
        $res=$this->execute($url,"userid转换成openid接口",$data);
        return $res;

    }
}