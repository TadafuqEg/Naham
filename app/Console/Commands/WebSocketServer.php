<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
//use MyApp\Chat;
use App\WebSockets\Chat; 

class WebSocketServer extends Command
{
    protected $signature = 'websockets:init';
    protected $description = 'Start the WebSocket server';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {   $loop = Loop::get();
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Chat($loop)
                )
            ),
            8080
        );

        $this->info('WebSocket server started on port 8080');
        $server->run();
    }
}