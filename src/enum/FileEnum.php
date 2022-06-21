<?php


namespace okcoder\think\filesystem\enum;


class FileEnum
{
    /**
     * 渠道
     */
    const ENUM_CHANNEL_LOCAL   = 1;  // 本地
    const ENUM_CHANNEL_NORMAL  = 1;  // 外网url
    const ENUM_CHANNEL_QI_NIU  = 2;  // 七牛云
    const ENUM_CHANNEL_ALI_YUN = 3;  // 阿里云


    /**
     * 文件类型
     */
    const ENUM_TYPE_IMAGE = 1;          // 图片
    const ENUM_TYPE_VIDEO = 2;          // 视频
    const ENUM_TYPE_AUDIO = 3;          // 音频
    const ENUM_TYPE_ZIP   = 4;          // ZIP压缩包
}