<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Elasticsearch\ClientBuilder;

class Logs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $log_info;
    protected $es_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $params)
    {
        $this->log_info = $params;
        $this->es_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = ClientBuilder::create()->build();
        $this->checkUser();
        $params = [
            'index' => 'logs',
            'type' => 'logs',
            'id' => $this->es_id,
            'body' => $this->log_info
        ];
        $client->index($params);
    }

    public function checkUser()
    {
//        检查用户是否存在用户表中
    }
}
