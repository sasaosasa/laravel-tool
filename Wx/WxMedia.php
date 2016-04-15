<?php
/**
 * Created by PhpStorm.
 * User: 单线程
 * Date: 2016/1/19
 * Time: 15:12
 */

namespace Tool\Wx;


class WxMedia extends WxUtil
{
    /**
     * 上传临时素材文件
     */
    public function downloadImg($media_id)
    {
        $accessToken = $this->getAccessToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/media/get?access_token=$accessToken&media_id=$media_id";
        return $this->getImage($url);
    }

    public function getImage($url, $query = '', $setHeader = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);//设置URL
        curl_setopt($ch, CURLOPT_POST, 1);//post
        curl_setopt($ch, CURLOPT_HTTPHEADER, $setHeader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);//传递一个作为HTTP “POST”操作的所有数据的字符串
        curl_setopt($ch, CURLOPT_HEADER, 1);//返回response头部信息
        curl_setopt($ch, CURLOPT_NOBODY, 0);//不返回response body内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//不直接输出response
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//如果返回的response 头部中存在Location值，就会递归请求
        $response = curl_exec($ch);

        if (!$response) {
            _pack(curl_error($ch));
        }


        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200') {
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            //$header = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
        } else {
            //错误
            _pack($response);
            exit;
        }
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $path = "./php/storage/wxCache/qy/img/" . date("Y-m-d") . '/';
        switch ($content_type) {
            case "image/jpeg":
                $path .= create_guid() . '.jpg';
                break;
            case "image/png":
                $path .= create_guid() . '.png';
                break;
            default:
                _pack("上传图片不允许！");
        }
        $this->mkDirs(dirname($path));
        curl_close($ch);//关闭
        $fp = @fopen($path, 'a');
        fwrite($fp, $body);
        fclose($fp);
        return $path;
    }

    function getImage2($url, $filename = '', $type = 0)
    {
        if ($url == '') {
            return false;
        }
        if ($filename == '') {
            $ext = strrchr($url, '.');
            if ($ext != '.gif' && $ext != '.jpg') {
                return false;
            }
            $filename = time() . $ext;
        }
        //文件保存路径
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $img = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $img = ob_get_contents();
            ob_end_clean();
        }
        $size = strlen($img);
        //文件大小

    }
}