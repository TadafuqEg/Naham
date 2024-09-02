<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
//use MyApp\Chat;
use App\WebSockets\Chat; 

class WebSocketServer extends Command
{
    protected $signature = 'websockets:serve';
    protected $description = 'Start the WebSocket server';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new Chat()
                )
            ),
            443
        );

        $this->info('WebSocket server started on port 443');
        $server->run();
    }
}