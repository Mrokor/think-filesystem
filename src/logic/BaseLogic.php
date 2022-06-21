<?php


namespace okcoder\think\filesystem\logic;


use okcoder\think\filesystem\enum\FileEnum;

class BaseLogic
{
    /**
     * 获取URL不含域名地址
     * @param string $url
     * @return false|mixed|string
     */
    public static function getKeyByUrl(string $url)
    {
        if (str_contains($url, 'http://') || strpos($url, 'https://') !== false) {
            $arr = parse_url($url);
            $url = substr($arr['path'], 1);
        }
        if (str_starts_with($url, '/')) {
            $url = substr($url, 1);
        }
        return $url;
    }

    /**
     * @param $file
     * @return mixed
     */
    public static function parseFileUrl($file)
    {
        $disks = config('filesystem.disks');
        switch ($file['channel']) {
            case 0:
            default:
                break;
            case FileEnum::ENUM_CHANNEL_QI_NIU:
                $file['url'] = $disks['qiniu']['url'] . '/' . $file['url'];
                break;
            case FileEnum::ENUM_CHANNEL_ALI_YUN:
                $file['url'] = $disks['aliyun']['url'] . '/' . $file['url'];
                break;
            case FileEnum::ENUM_CHANNEL_LOCAL:
                $file['url'] = $disks['local']['root'] . '/' . $file['url'];
                break;
        }
        return $file;
    }

}