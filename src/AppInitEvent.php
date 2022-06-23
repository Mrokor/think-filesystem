<?php

namespace okcoder\think\filesystem;

use think\facade\Route;


class AppInitEvent
{
    public function handle()
    {
        self::registerRoute();
    }

    /**
     *  注册路由
     */
    private static function registerRoute()
    {
        $route_prefix = config("filesystem.route_prefix") ?: 'okcoder/filesystem';
        Route::group($route_prefix, function () {
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



