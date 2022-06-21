<?php


namespace okcoder\think\filesystem\enum;


class FileTranscodingEnum
{
    /**
     * 转码状态
     */
    const ENUM_STATE_HANDLE_ING = 1;  // 转码中
    const ENUM_STATE_SUCCESS    = 2;  // 转码成功
    const ENUM_STATE_FAIL       = 3;  // 转码失败


    const ENUM_VIDEO_PREVIEW                  = 1;   // 视频压缩      删除原视频
    const ENUM_VIDEO_WATER                    = 2;   // 视频水印      保留原视频
    const ENUM_VIDEO_PREVIEW_WATER            = 3;   // 视频压缩加水印 保留原视频
    const ENUM_VIDEO_265_TO_264               = 4;   // 265转264     删除265原视频
    const ENUM_VIDEO_265_TO_264_PREVIEW       = 5;   // 265转264压缩  删除265原视频
    const ENUM_VIDEO_265_TO_264_WATER         = 5;   // 265转264水印  删除265原视频
    const ENUM_VIDEO_265_TO_264_PREVIEW_WATER = 6;   // 265转264压缩水印  删除265原视频
    const ENUM_IMAGE_PREVIEW_COVER            = 10;  // 图片压缩  删除原图
    const ENUM_IMAGE_PREVIEW                  = 11;  // 图片压缩  保留原图
}