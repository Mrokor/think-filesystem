<?php


namespace okcoder\think\filesystem\model;


use okcoder\think\filesystem\jobs\FileTranscodingJob;
use think\facade\Queue;
use think\Model;

/**
 * @property int id
 * @property int type
 * @property int state
 * @property array handle_result
 * @property string url
 * @property string transcoding_id
 */
class FileTranscodingModel extends Model
{
    protected $name = 'file_transcoding';

    protected $type = [
        'id'            => 'integer',
        'type'          => 'integer',
        'file_id'       => 'integer',
        'state'         => 'integer',
        'handle_result' => 'json',
    ];

    public static function onAfterInsert(FileTranscodingModel $model)
    {
        Queue::push(FileTranscodingJob::class, $model->id, 'FileTranscodingJob');
    }

}