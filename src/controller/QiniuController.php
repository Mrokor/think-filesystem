<?php


namespace okcoder\think\filesystem\controller;


use Exception;
use okcoder\think\filesystem\enum\FileEnum;
use okcoder\think\filesystem\enum\FileTranscodingEnum;
use okcoder\think\filesystem\logic\QiniuLogic;
use okcoder\think\filesystem\model\FileModel;
use okcoder\think\filesystem\model\FileTranscodingModel;
use think\facade\Log;
use think\Response;
use think\response\Json;

class QiniuController
{
    /**
     * 获取七牛上传凭证
     * @return Response
     */
    public function getConfig(): Response
    {
        $type   = input('type', 0, 'intval');
        $effect = input('effect', 0, 'intval');
        $data   = QiniuLogic::getConfig($type, $effect);
        return apiReturn($data);
    }

    /**
     * [配置]上传回调
     */
    public function postCallback(): Json
    {

        Log::write(input(), 'think-filesystem');
        try {
            $input['type']    = input('post.type', 0, 'intval');
            $input['effect']  = input('post.effect', 0, 'intval');
            $input['channel'] = input('post.channel', 0, 'intval');
            $input['url']     = $input['original_url'] = input('post.key', '', 'trim');
            $input['width']   = input('post.width', 0, 'intval');
            $input['height']  = input('post.height', 0, 'intval');
            $input['size']    = input('post.size', 0, 'intval');
            $input['ext']     = input('post.ext', '', 'trim');
            $codec_name       = input('post.codec_name', '', 'trim');
            if (in_array($input['type'], [FileEnum::ENUM_TYPE_AUDIO, FileEnum::ENUM_TYPE_VIDEO])) {
                $input['duration'] = input('post.duration', 0);
            }

            if (!$input['url']) exit('fail');

            $fileModel = FileModel::create($input);

            if ($input['type'] == FileEnum::ENUM_TYPE_IMAGE) {
                if ($input['size'] > 30 * 1024) {
                    FileTranscodingModel::create(['type' => FileTranscodingEnum::ENUM_IMAGE_PREVIEW, 'file_id' => $fileModel->id]);
                }
            } elseif ($input['type'] == FileEnum::ENUM_TYPE_VIDEO) {
                $_265To264 = config('filesystem.265To264') ?? false;
                if ($_265To264 && QiniuLogic::isH265ByStr($codec_name)) {
                    if ($input['size'] > 5 * 1024 * 1024) {
                        FileTranscodingModel::create(['type' => FileTranscodingEnum::ENUM_VIDEO_265_TO_264_PREVIEW_WATER, 'file_id' => $fileModel->id]);
                    } else {
                        FileTranscodingModel::create(['type' => FileTranscodingEnum::ENUM_VIDEO_265_TO_264_WATER, 'file_id' => $fileModel->id]);
                    }
                } else {
                    if ($input['size'] > 5 * 1024 * 1024) {
                        FileTranscodingModel::create(['type' => FileTranscodingEnum::ENUM_VIDEO_PREVIEW_WATER, 'file_id' => $fileModel->id]);
                    } else {
                        $imageWater = config('filesystem.disks.qiniu.videoWater') ?? null;
                        if ($imageWater) FileTranscodingModel::create(['type' => FileTranscodingEnum::ENUM_VIDEO_WATER, 'file_id' => $fileModel->id]);
                    }
                }
            }
        } catch (Exception $e) {
            Log::write($e->getMessage(), 'think-filesystem');
        }
        return \json($fileModel);
    }

    /**
     * 七牛云转码异步通知
     * @throws Exception
     */
    public function postTranscodingUrl()
    {
        $data                 = request()->post();
        $fileTranscodingModel = FileTranscodingModel::where(['transcoding_id' => $data['id'] ?? 0])->order('id desc')->find();
        if (!$fileTranscodingModel) {
            Log::write($data, 'transcodingUrl');
            exit();
        }
        if ($data['code'] != '0' || $data['items'][0]['code'] != '0') {
            Log::write($data, 'transcodingUrl_状态码错误');
            $fileTranscodingModel->state         = FileTranscodingEnum::ENUM_STATE_FAIL;
            $fileTranscodingModel->handle_result = $data;
            $fileTranscodingModel->save();
            exit();
        }
        $item = $data['items'][0];

        $fileTranscodingModel->state         = FileTranscodingEnum::ENUM_STATE_SUCCESS;
        $fileTranscodingModel->url           = $item['key'];
        $fileTranscodingModel->handle_result = $data;
        $fileTranscodingModel->save();

        switch ($fileTranscodingModel->type) {
            default:
                break;
            case FileTranscodingEnum::ENUM_IMAGE_PREVIEW_COVER:
                $fileModel      = FileModel::find($fileTranscodingModel->file_id);
                $fileModel->url = $item['key'];
                $fileModel->save();
                // 删除原图
                QiniuLogic::deleteOriginalUrl($fileModel);
                break;
            case FileTranscodingEnum::ENUM_IMAGE_PREVIEW:
            case FileTranscodingEnum::ENUM_VIDEO_WATER:
            case FileTranscodingEnum::ENUM_VIDEO_PREVIEW_WATER:
                // 保留原文件
                $fileModel      = FileModel::find($fileTranscodingModel->file_id);
                $fileModel->url = $item['key'];
                $fileModel->save();
                break;
            case FileTranscodingEnum::ENUM_VIDEO_265_TO_264_WATER:
                $fileModel = FileModel::find($fileTranscodingModel->file_id);
                // 删除265视频
                QiniuLogic::deleteOriginalUrl($fileModel);

                $fileModel->original_url = $item['key'];
                $fileModel->url          = $item['key'];
                $fileModel->save();
                $imageWater = config('filesystem.disks.qiniu.videoWater') ?? null;
                if ($imageWater) FileTranscodingModel::create(['type' => FileTranscodingEnum::ENUM_VIDEO_WATER, 'file_id' => $fileModel->id]);
                break;
            case FileTranscodingEnum::ENUM_VIDEO_265_TO_264_PREVIEW_WATER:
                $fileModel = FileModel::find($fileTranscodingModel->file_id);
                // 删除265视频
                QiniuLogic::deleteOriginalUrl($fileModel);

                $fileModel->original_url = $item['key'];
                $fileModel->url          = $item['key'];
                $fileModel->save();
                FileTranscodingModel::create(['type' => FileTranscodingEnum::ENUM_VIDEO_PREVIEW_WATER, 'file_id' => $fileModel->id]);
                break;
        }
        exit();
    }
}