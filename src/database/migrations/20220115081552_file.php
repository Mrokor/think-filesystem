<?php

use think\console\Output;
use think\migration\Migrator;
use think\migration\db\Column;

class File extends Migrator
{
    private $table = 'file';

    public function up()
    {
        $table = $this->table($this->table, [
            'engine'    => 'InnoDB',
            'comment'   => '公开资源库',
            'encoding'  => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'id'        => false
        ]);
        if (!$table->exists()) {
            $table
                ->addColumn('channel', 'boolean', ['null' => false, 'default' => 0, 'comment' => '渠道'])
                ->addColumn('type', 'integer', ['null' => false, 'default' => 0, 'comment' => '文件类型'])
                ->addColumn('url', 'string', ['null' => false, 'default' => '', 'limit' => 500, 'comment' => '展示地址 图片则为源地址一样视频含水印/瘦身等'])
                ->addColumn(Column::string('original_url')->setNull(false)->setDefault('')->setLimit(500)->setComment('源地址 实际上传的地址 如果视频是h265则自动转为h264'))
                ->addColumn(Column::smallInteger('duration')->setSigned(false)->setDefault(0)->setNull(false)->setComment('视频秒长'))
                ->addColumn(Column::smallInteger('width')->setSigned(false)->setDefault(0)->setNull(false)->setComment('图片宽度'))
                ->addColumn(Column::smallInteger('height')->setSigned(false)->setDefault(0)->setNull(false)->setComment('图片高度'))
                ->addColumn(Column::boolean('url_allow_delete')->setSigned(true)->setDefault(0)->setComment('预览文件是否允许删除'))
                ->addColumn(Column::boolean('original_url_allow_delete')->setSigned(true)->setDefault(0)->setComment('原文件是否允许删除'))
                ->addColumn(Column::smallInteger('effect')->setSigned(false)->setDefault(0)->setNull(false)->setComment('作用'))
                ->addColumn(Column::string('ext')->setLimit(10)->setDefault('')->setNull(false)->setComment('文件名后缀'))
                ->addTimestamps()
                ->addColumn(Column::timestamp('delete_time')->setNull(true)->setDefault(null)->setComment('删除时间'))
                ->addIndex(['channel'])
                ->addIndex(['type'])
                ->create();
        } else {
            $out = new Output();
            $out->info("表[ {$this->table} ]已存在");
        }
    }

    public function down()
    {
        $this->dropTable($this->table);
    }
}
