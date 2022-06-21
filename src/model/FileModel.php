<?php

namespace okcoder\think\filesystem\model;

use okcoder\think\filesystem\logic\BaseLogic;
use think\Model;
use think\model\concern\SoftDelete;

/**
 * 文件管理模型
 * @property int id
 * @property int channel
 * @property string original_url
 * @property string url
 */
class FileModel extends Model
{
    use SoftDelete;

    protected $name = 'file';

    protected $deleteTime = 'delete_time';

    protected $dateFormat = false;

    protected $hidden = ['url_allow_delete', 'original_url_allow_delete', 'create_time', 'update_time', 'delete_time'];

    protected $type = [
        'id'                        => 'integer',
        'type'                      => 'integer',
        'user_id'                   => 'integer',
        'channel'                   => 'integer',
        'url_allow_delete'          => 'boolean',
        'original_url_allow_delete' => 'boolean',
    ];


    public function getUrlAttr($v, $data)
    {
        $data = BaseLogic::parseFileUrl($data);
        return $data['url'];
    }


    public function getDurationAttr($value)
    {
        return $value / 1000;
    }

    public function setDurationAttr($value)
    {
        return $value * 1000;
    }
}