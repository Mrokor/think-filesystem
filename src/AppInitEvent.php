<?php

namespace okcoder\think\filesystem;

use think\facade\Route;


class AppInitEvent
{
    public function handle()
    {
        self::registerRoute();
    }

    private static function registerRoute()
    {
        Route::group("okcoder/filesystem", function () {
            Route::group("qiniu", function () {
                // 获取上传凭证
                Route::get("get_config", "\\okcoder\\think\\filesystem\\controller\\QiniuController@getConfig");
                // [配置]上传回调
                Route::any("post_callback", "\\okcoder\\think\\filesystem\\controller\\QiniuController@postCallback");
                // [配置]转码回调
                Route::post("post_transcoding_url", "\\okcoder\\think\\filesystem\\controller\\QiniuController@postTranscodingUrl");
            });
        });
    }
}



