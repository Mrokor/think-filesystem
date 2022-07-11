<?php


namespace okcoder\think\filesystem\logic;

use Exception;
use okcoder\think\filesystem\enum\FileEnum;
use okcoder\think\filesystem\model\FileModel;
use Qiniu\Storage\BucketManager;
use think\facade\Log;

class QiniuLogic extends BaseLogic
{
    /**
     * 获取上传凭证
     * @param int $type 文件类型
     * @param int $effect 文件作用标记
     * @return array
     */
    public static function getConfig(int $type, int $effect = 0): array
    {
        $auth             = \app('filesystem')->disk('qiniu')->getAdapter();
        $saveKey          = $type . '/${mon}${day}${hour}${min}${sec}/${etag}${ext}';
        if ($effect) $saveKey = $effect . '/' . $saveKey;
        $returnBodyCommon = self::getReturnBody($type, $effect);
        $route_prefix = config("filesystem.route_prefix") ?: 'okcoder/filesystem';
        $policy           = [
            'forceSaveKey'     => true, // 忽略客户端指定的key，强制使用saveKey进行文件命名
            'saveKey'          => $saveKey,
            'callbackUrl'      => request()->domain() . '/index.php/'.$route_prefix.'/qiniu/post_callback',
            'callbackBody'     => json_encode($returnBodyCommon, JSON_UNESCAPED_UNICODE),
            'callbackBodyType' => 'application/json'
        ];
        $expire_in        = 3600;
        $token            = $auth->getUploadToken(null, $expire_in, $policy);
        return ['token' => $token, 'type' => $type, 'channel' => FileEnum::ENUM_CHANNEL_QI_NIU, 'expire_in' => $expire_in];
    }

    /**
     * 定义回调字段格式
     * @param int $type
     * @param int $effect
     * @return array
     */
    public static function getReturnBody(int $type, int $effect = 0): array
    {
        $returnBodyCommon = [
            "key"     => "$(key)",
            "channel" => FileEnum::ENUM_CHANNEL_QI_NIU,
            "effect"  => $effect,
            "type"    => $type,
            "ext"     => "$(ext)"
        ];
        if ($type === FileEnum::ENUM_TYPE_IMAGE) {
            $returnBodyCommon['width']  = "$(imageInfo.width)";
            $returnBodyCommon['height'] = "$(imageInfo.height)";
            $returnBodyCommon['format'] = "$(imageInfo.format)";
            $returnBodyCommon['size']   = "$(imageInfo.size)";
        }
        if ($type === FileEnum::ENUM_TYPE_VIDEO) {
            $returnBodyCommon['width']      = "$(avinfo.video.width)";
            $returnBodyCommon['height']     = "$(avinfo.video.height)";
            $returnBodyCommon['duration']   = "$(avinfo.video.duration)";
            $returnBodyCommon['codec_name'] = "$(avinfo.video.codec_name)";
            $returnBodyCommon['pix_fmt']    = "$(avinfo.video.pix_fmt)";
            $returnBodyCommon['size']       = "$(avinfo.format.size)";
        }
        if ($type === FileEnum::ENUM_TYPE_AUDIO) {
            $returnBodyCommon['codec_name'] = "$(avinfo.codec_name)";
            $returnBodyCommon['codec_type'] = "$(avinfo.codec_type)";
            $returnBodyCommon['duration']   = "$(avinfo.duration)";
            $returnBodyCommon['size']       = "$(avinfo.format.size)";
        }
        return $returnBodyCommon;
    }

    /**
     * 通过字符串查是否是 H265视频
     * @param string $str
     * @return bool
     */
    public static function isH265ByStr(string $str): bool
    {
        $str = strtolower($str);
        if (str_contains($str, '265')) return true;
        if (str_contains($str, 'hevc')) return true;
        return false;
    }

    /**
     * 删除文件
     * @param FileModel $fileModel
     * @return bool
     */
    public static function deleteOriginalUrl(FileModel $fileModel): bool
    {
        $config        = config('filesystem.disks.qiniu');
        $key           = self::getKeyByUrl($fileModel->original_url);
        $auth          = app('filesystem')->disk('qiniu')->getAdapter();
        $bucketManager = new BucketManager($auth->getAuth());
        try {
            $ret = $bucketManager->delete($config['bucket'], $key);
            if (!$ret) throw new Exception("没有返回消息");
            if ($ret->code == 200) return true;
            if ($ret->code == 612) throw new Exception("待删除文件不存在");
            if ($ret->code == 599) throw new Exception("服务端操作失败");
            if ($ret->code == 401) throw new Exception("管理凭证无效");
            if ($ret->code == 400) throw new Exception("请求报文格式错误");
            throw new Exception($ret->code);
        } catch (Exception $e) {
            Log::write($fileModel->toArray(), '七牛文件删除错误');
            return false;
        }
    }
}