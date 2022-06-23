<?php

use think\facade\Env;

return [
    // 默认磁盘
    'default'      => Env::get('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'        => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息    composer require thans/thinkphp-filesystem-cloud
        'qiniu'  => [
            'type'       => 'qiniu',
            'accessKey'  => env('FILESYSTEM_QINIU_ACCESS_KEY', ''),
            'secretKey'  => env('FILESYSTEM_QINIU_SECRET_KEY', ''),
            'bucket'     => env("FILESYSTEM_QINIU_BUCKET", ''),
            'url'        => env('FILESYSTEM_QINIU_URL', ''),//不要斜杠结尾，此处为URL地址域名。
            // 视频水印参数 如果不需要水印可传空
            'videoWater' => '',// \Qiniu\base64_urlSafeEncode("kodo://" . env("FILESYSTEM_QINIU_BUCKET", '') . '/system/water.png') . '/wmGravity/SouthEast/wmScale/0.2/wmOffsetX/-20/wmOffsetY/-20',
            // 图片瘦身
            'imageZip'   => 'imageView2/2/w/1440/format/jpg/interlace/1/q/40/ignore-error/1|imageslim',
            // 转码队列
            'pipelines'  => [
                'videoWater'        => null,   // 生成视频预览加水印
                'videoPreviewWater' => null,   // 生成视频预览压缩加水印
                'h265ToH264'        => null,   // 265视频转码264
                'imageZip'          => null,   // 图片瘦身
            ]
        ],
        'aliyun' => [
            'type'         => 'aliyun',
            'accessId'     => '******',
            'accessSecret' => '******',
            'bucket'       => 'bucket',
            'endpoint'     => 'oss-cn-hongkong.aliyuncs.com',
            'url'          => 'http://oss-cn-hongkong.aliyuncs.com',//不要斜杠结尾，此处为URL地址域名。
        ],
        'qcloud' => [
            'type'            => 'qcloud',
            'region'          => '***', //bucket 所属区域 英文
            'appId'           => '***', // 域名中数字部分
            'secretId'        => '***',
            'secretKey'       => '***',
            'bucket'          => '***',
            'timeout'         => 60,
            'connect_timeout' => 60,
            'cdn'             => '',    // 您的 CDN 域名
            'scheme'          => 'https',
            'read_from_cdn'   => false,
        ]
    ],
    // 路由前缀
    'route_prefix' => 'okcoder/filesystem',
    // 是否开启265视频转码成264视频
    '265To264'     => true
];
