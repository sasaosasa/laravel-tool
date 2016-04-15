<?php
/**
 * Created by PhpStorm.
 * User: 单线程
 * Date: 2016/4/1
 * Time: 17:02
 */

namespace Tool\Wx;


class WxExecute extends WxToken
{
    /**
     * 执行返回
     */
    public function execute_return($uri, $data = "", $reload = false)
    {
        $url = $this->getRequestUrl($uri, $reload);
        $res = $this->curl($url, $this->json_encode_cn($data));
        $check_data = $this->checkData($res);
        if ($check_data['success']) {
            log_file("log/wx/execute_return", "执行返回", $data, $check_data['res'], $uri, "成功！");
            return $check_data;
        } else {
            if ($reload) {
                //重发的
                if ($this->isInvalidAccessToken($check_data['error_code'])) {
                    $content = "重发后ACCESS_TOKE一样失败！";
                } else {
                    $content = "重发后失败！";
                }
                log_file("error/wx/execute_return", "执行返回", $data, $check_data['res'], $uri, $content);
                return $check_data;
            } else {
                //不是重发的
                if ($this->isInvalidAccessToken($check_data['error_code'])) {
                    //ACCESS_TOKEN错误,重发
                    return $this->execute_return($uri, $data, true);
                } else {
                    //有错误！！！
                    log_file("error/wx/execute_return", "执行返回", $data, $check_data['res'], $uri, "失败！");
                    return $check_data;
                }
            }
        }
    }

    /**
     * 检查微信返回的数据
     */
    public function checkData($res)
    {
        $return = [
            "success" => false,
            "res" => $res,
            "error_code" => -100
        ];
        if ($res == false) {
            $return['res'] = "请求失败！";
            return $return;
        }
        $res_arr = json_decode($res, true);
        if ($res_arr == false || !is_array($res_arr) || (isset($res_arr['errcode']) && $res_arr['errcode'] !== 0)) {
            //请求失败
            if (isset($res_arr['errcode'])) {
                $return['error_code'] = $res_arr['errcode'];
            }
            return $return;
        } else {
            $return['success'] = true;
            return $return;
        }
    }
}