<?php

use think\console\Output;
use think\migration\Migrator;
use think\migration\db\Column;

class FileTranscoding extends Migrator
{
    private $table = 'file_transcoding';

    public function up()
    {
        $table = $this->table($this->table, [
            'engine'    => 'InnoDB',
            'comment'   => '视频转码记录',
            'encoding'  => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
        ]);
        if (!$table->exists()) {
            $table
                ->addColumn(
                    Column::boolean('type')->setNull(false)->setSigned(false)->setDefault(0)->setComment("文件ID")
                )
                ->addColumn(
                    Column::integer('file_id')->setNull(false)->setSigned(false)->setDefault(0)->setComment("文件ID")
                )
                ->addColumn(
                    Column::string('transcoding_id')->setLimit(40)->setNull(false)->setDefault('')->setComment('转码队列ID')
                )
                ->addColumn(
                    Column::string('url')->setNull(false)->setDefault('')->setComment('转码后视频URL')
                )
                ->addColumn(
                    Column::boolean('state')->setSigned(true)->setDefault(0)->setComment('0不需要转码 1转码成功 2 等待转码 3转码中 4转码失败')
                )
                ->addColumn(
                    Column::string('rules')->setNull(false)->setDefault('')->setLimit(500)->setComment('转码参数')
                )
                ->addColumn(
                    Column::text('handle_result')->setComment('处理结果 json')
                )
                ->addTimestamps()
                ->addIndex(['transcoding_id'])
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
