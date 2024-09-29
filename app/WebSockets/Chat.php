<?php
namespace App\WebSockets;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $loop; 
    public function __construct($loop) {
        $this->clients = new \SplObjectStorage;
        $this->loop = $loop; 
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);

        $userId = $queryParams['user_id'] ?? null;

        $userToken = $queryParams['token'] ?? null;
        $user = User::where('id', $userId)->first();
      

        if (!$user) {
            echo "Invalid user. Connection refused.\n";
            $conn->close();
            return;
        }else{
           
            $tokenParts = explode('|', $userToken);
            $tokenId = $tokenParts[0];
            $tokenValue = $tokenParts[1];
            
            // // Find the token by ID and user
             $token = $user->tokens()->where('id', $tokenId)->first();
            // //dd($userToken,$tokenId,$tokenValue,$token->token);
            
            // dd(Crypt::encryptString($tokenValue),$token->token);
             if ($token && hash('sha256', $tokenValue) ===  $token->token) {
                // Token matches
                $this->clients->attach($conn, $userId);
                $date_time=date('Y-m-d h:i:s a');
                //$conn->send(json_encode(['type' => 'ping']));
                $this->periodicPing($conn);
                //echo "New connection! ({$conn->resourceId})\n";
                echo "[ {$date_time} ],New connection! User ID: {$userId}, Connection ID: ({$conn->resourceId})\n";
            } else {
                // Token does not match
                echo "Token does not match.";
                $conn->close();
                return;
            }
        }
        
    }

    private function periodicPing(ConnectionInterface $conn) {
        $timer = 60; // Send a ping every 60 seconds
    
        $this->loop->addPeriodicTimer($timer, function() use ($conn) {
            try {
                // Try sending a ping message, if connection is closed, it'll throw an error
                $conn->send(json_encode(['type' => 'ping'])); // Send a ping
                $date_time = date('Y-m-d h:i:s a');
                echo "[ {$date_time} ], Ping sent to Connection {$conn->resourceId}\n";
            } catch (\Exception $e) {
                // If there's an error sending the ping, the connection is probably closed
                $date_time = date('Y-m-d h:i:s a');
                echo "[ {$date_time} ], Connection {$conn->resourceId} has closed during ping\n";
            }
        });
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        
        // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
        //     , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        $data = json_decode($msg, true);
        if(array_key_exists('pong', $data)){
            echo sprintf("sss");
        }else{
            if (array_key_exists('to_user_id', $data)) {
           
                $toUserId = $data['to_user_id'];
            }
            if (array_key_exists('to_group_id', $data)) {
               
                $toGroupId = $data['to_group_id'];
            }
    
    
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $clientUserId = $this->clients[$client];
                
                    $clientGroupId = User::where('id',intval($clientUserId))->first()->group_id;
                
                    if (array_key_exists('to_user_id', $data)) {
                    
                        if ($clientUserId == $toUserId) {
                            $client->send($msg);
                            $date_time=date('Y-m-d h:i:s a');
                            echo sprintf('[ %s ],Message "%s" sent from user %d sent to user %d' . "\n",$date_time,$msg, $this->clients[$from], $toUserId);
                        }
                        
                    }elseif (array_key_exists('to_group_id', $data)) {
                    
                        if ($clientGroupId == $toGroupId) {
                            $client->send($msg);
                            $date_time=date('Y-m-d h:i:s a');
                            echo sprintf('[ %s ],Message "%s" sent from user %d sent to group %d' . "\n",$date_time,$msg, $this->clients[$from], $toGroupId);
                        }
                       
                    }
                }
                
            }
        }
        
        
        //$data = json_decode($msg, true);
        // if ($data['type'] === 'candidate') {
        //     // Broadcast the ICE candidate to all clients except the sender
        //     foreach ($this->clients as $client) {
        //         if ($from !== $client) {
        //             $client->send(json_encode($data));
        //         }
        //     }
        // }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        $date_time=date('Y-m-d h:i:s a');
        echo "[ {$date_time} ],Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}