<?php


namespace okcoder\think\filesystem\jobs;

use okcoder\think\filesystem\enum\FileEnum;
use okcoder\think\filesystem\enum\FileTranscodingEnum;
use okcoder\think\filesystem\logic\BaseLogic;
use okcoder\think\filesystem\model\FileModel;
use okcoder\think\filesystem\model\FileTranscodingModel;
use Qiniu\Processing\PersistentFop;
use think\queue\Job;
use function Qiniu\base64_urlSafeEncode;

/**
 * 视频转码(七牛)
 * Class FileTranscodingJob
 * @package app\common\jobs
 *
 */
class FileTranscodingJob
{
    public function fire(Job $job, $fileTranscodingId)
    {
        echo "\n =========== ";

        $job->delete();

        $fileTranscodingModel = FileTranscodingModel::find($fileTranscodingId);

        if (!$fileTranscodingModel) {
            echo "\n 转码记录不存在\n";
            return;
        }

        $fileModel = FileModel::find($fileTranscodingModel->file_id);

        if (!$fileModel) {
            echo "\n 文件不存在\n";
            return;
        }

        if ($fileModel->channel != FileEnum::ENUM_CHANNEL_QI_NIU) {
            echo "\n 不是七牛文件\n";
            return;
        }

        $config     = config('filesystem.disks.qiniu');
        $auth       = app('filesystem')->disk('qiniu')->getAdapter();
        $fopManager = new PersistentFop($auth->getAuth());

        $key       = BaseLogic::getKeyByUrl($fileModel->original_url);
        $route_prefix     = config("filesystem.route_prefix") ?: 'okcoder/filesystem';
        $notifyUrl = 'https://' . config('app.app_host') . "/index.php/".$route_prefix."/qiniu/post_transcoding_url";
        $pipeline  = null;

        switch ($fileTranscodingModel->type) {
            default:
//            case FileTranscodingEnum::ENUM_VIDEO_PREVIEW:
//            case FileTranscodingEnum::ENUM_VIDEO_265_TO_264:
//            case FileTranscodingEnum::ENUM_VIDEO_265_TO_264_PREVIEW:
                echo "\n 未知的转码类型";
                $fileTranscodingModel->state         = FileTranscodingEnum::ENUM_STATE_FAIL;
                $fileTranscodingModel->handle_result = '未知的转码类型';
                $fileTranscodingModel->save();
                echo "\n";
                return;
            case FileTranscodingEnum::ENUM_VIDEO_WATER:
                echo "\n 生成视频预览加水印";
                if (!$config['videoWater']) return;
                $fops     = "avthumb/mp4";
                $fops     .= '/wmImage/' . $config['videoWater'];
                $fops     .= '|saveas/' . base64_urlSafeEncode($config['bucket'] . ':video_water/' . $key);
                $pipeline = $config['pipelines']['videoWater'] ?? null;
                break;
            case FileTranscodingEnum::ENUM_VIDEO_PREVIEW_WATER:
                echo "\n 生成视频预览压缩加水印";
                $fops = "avthumb/mp4";
                $fops .= '/crf/28';

                if ($config['videoWater']) $fops .= '/wmImage/' . $config['videoWater'];

                $fops .= '|saveas/' . base64_urlSafeEncode($config['bucket'] . ':video_preview_water/' . $key);

                $pipeline = $config['pipelines']['videoPreviewWater'] ?? null;
                break;
            case FileTranscodingEnum::ENUM_VIDEO_265_TO_264_WATER:
            case FileTranscodingEnum::ENUM_VIDEO_265_TO_264_PREVIEW_WATER:
                echo "\n 265视频转码264";
                // 转mp4格式
                $fops = "avthumb/mp4";
                // H265视频需要转码成H264
                $fops .= "/vcodec/libx264";
                // SDR
                $fops .= "/sdr/1";

                if ($config['videoWater']) $fops .= '/wmImage/' . $config['videoWater'];

                $fops .= '|saveas/' . base64_urlSafeEncode($config['bucket'] . ':265_264/' . $key);

                $pipeline = $config['pipelines']['h265ToH264'] ?? null;

                break;
            case FileTranscodingEnum::ENUM_IMAGE_PREVIEW:
            case FileTranscodingEnum::ENUM_IMAGE_PREVIEW_COVER:
                echo "\n 图片瘦身";
                if (!$config['imageZip']) return;
                $fops     = $config['imageZip'];
                $fops     .= '|saveas/' . base64_urlSafeEncode($config['bucket'] . ':image_preview/' . $key);
                $pipeline = $config['pipelines']['imageZip'] ?? null;
                break;
        }

        list($id, $error) = $fopManager->execute($config['bucket'], $key, $fops, $pipeline, $notifyUrl, true);
        $fileTranscodingModel->rules = $fops;
        if ($error) {
            $fileTranscodingModel->state         = FileTranscodingEnum::ENUM_STATE_FAIL;
            $fileTranscodingModel->handle_result = $error;
            $fileTranscodingModel->save();
            echo "\n";
            return;
        }
        $fileTranscodingModel->state          = FileTranscodingEnum::ENUM_STATE_HANDLE_ING;
        $fileTranscodingModel->transcoding_id = $id;
        $fileTranscodingModel->save();
        echo "\n";
    }
}