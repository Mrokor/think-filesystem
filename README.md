# think-filesystem

### 安装composer包

```composer require okcoder\think-filesystem```

### 创建表

```php think filesystem:table```

### 配置应用 app/event.php

```php
return [
    'listen'    =>  [
        'AppInit'   =>  [
            \okcoder\think\filesystem\AppInitEvent::class
        ]
    ]
];
```

### 配置文件 config/filesystem.php

```php
return [
    'disks' =>  [
        'qiniu'  => [
            'type'          => 'qiniu',
            'accessKey'     => env('FILESYSTEM.QINIU.ACCESS_KEY', ''),
            'secretKey'     => env('FILESYSTEM.QINIU.SECRET_KEY', ''),
            'bucket'        => env("FILESYSTEM.QINIU.BUCKET", ''),
            'url'           => env('FILESYSTEM.QINIU.URL', ''),//不要斜杠结尾，此处为URL地址域名。
            'videoWater'    => \Qiniu\base64_urlSafeEncode("kodo://" . env("FILESYSTEM.QINIU.BUCKET", '') . '/system/water.png') . '/wmGravity/SouthEast/wmScale/0.2/wmOffsetX/-20/wmOffsetY/-20',  // 视频水印
            'imageZip'      => 'imageView2/2/w/1440/format/jpg/interlace/1/q/40/ignore-error/1|imageslim', // 图片瘦身
            'pipelines'     => [    //  转码队列
                'videoWater'        =>  null,   // 生成视频预览加水印
                'videoPreviewWater' =>  null,   // 生成视频预览压缩加水印
                'h265ToH264'        =>  null,   // 265视频转码264
                'imageZip'          =>  null,   // 图片瘦身
            ]
        ],
    ]
];
```

> 开启转码需要配置app.app_host网站域名,支持https:   www.baidu.com
> 转码队列
```
    php think listen:queue --queue FileTranscodingJob
```

### 路由

#### 七牛云

##### 获取上传凭证

> GET /file/file/qiniu/get_config?type=&effect=

##### [配置]上传回调(可忽略)

> POST /file/file/qiniu/post_callback

##### [配置]转码回调(可忽略)

> POST /file/file/qiniu/post_transcoding_url
