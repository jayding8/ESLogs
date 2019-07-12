<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Elasticsearch\ClientBuilder;

class ESInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:init {op=ignore}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init default index of ES';

    const OP = ['ignore','force'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $op = $this->argument('op');
        if ( !in_array( $op , self::OP ) ){
            $this->error('Unknow op of `'.$op.'`');
            exit;
        }

        $client = ClientBuilder::create()->build();
        if ( $client->ping() ){
            $this->info('Ping ES success');
        }else{
            $this->error('Can not ping ES');
            exit;
        }

        $indexes = config('es.index');
        if ( !empty($indexes)  && is_array($indexes) ){
            foreach ( $indexes as $index => $v ){
                if ( $client->indices()->exists(['index' => $index]) ) {
                    if ( $op == 'ignore' ){
                        $this->error('The index `'.$index.'` already exist');
                        continue;
                    }elseif( $op == 'force' ){
                        $client->indices()->delete(['index' => $index]);
                        $this->info('Delete ES index `'.$index.'` success');
                    }
                }

                $data = [
                    'index' => $index,
                    'body' => $v['mapping']
                ];
                $client->indices()->create($data);
                $this->info('Create ES index `'.$index.'` success');

            }
        }else{
            $this->error('No default index,please add in config/es.php first!');
            exit;
        }
        $this->info('Init success');
    }
}
