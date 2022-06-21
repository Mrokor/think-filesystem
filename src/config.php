<?php
return [
    'qiniu' => [
        'accessKey' => env('FILE.QINIU.ACCESS_KEY', '_GUrc9YtXldGU8U-OtKaB4IqQWoOU3y6DQJVw_aN'),
        'secretKey' => env('FILE.QINIU.SECRET_KEY', 'oDIXwlBqvYv5JcXDcx7pfvohG6Xs5jPiQqIjHtxz'),
        'bucket' => env("FILE.QINIU.BUCKET", 'hellomadou'),
        'url' => env('FILE.QINIU.URL', 'https://cdn.hellomadou.com'), //不要斜杠结尾，此处为URL地址域名。
    ],
    'aliyun' => [
        'accessId' => env('FILE.ALIYUN.ACCESS_ID', ''),
        'accessSecret' => env('FILE.ALIYUN.ACCESS_SECRET', ''),
        'bucket' => env("FILE.ALIYUN.BUCKET", ''),
        'endpoint' => env("FILE.ALIYUN.ENDPOINT", 'oss-cn-hongkong.aliyuncs.com'),
        'url' => env('FILE.ALIYUN.URL', 'http://oss-cn-hongkong.aliyuncs.com'), //不要斜杠结尾，此处为URL地址域名。
    ],
];
