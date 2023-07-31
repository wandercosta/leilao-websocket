<?php

namespace Src;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use \PDO;
class Chat implements MessageComponentInterface
{
    protected $clients;
    protected $pdo;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->conexao = new \PDO('mysql:host=localhost;dbname=leilao', 'root', '');
        $this->conexao->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";

    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);

        $texto = "Online";
        $message = $data->message ?: $texto;



        if (isset($data->type) && $data->type === 'user_data') {




            $this->user_data[$from->resourceId] = [
                'user' => $data->user,
                'conversationId' => $data->conversationId,
                'userId' => $data->userId
            ];

            // Enviar mensagens antigas
            $conversationId = $data->conversationId;


            $oldMessages = $this->getOldMessages($data->conversationId, $data->userId);


            foreach ($oldMessages as $message) {

                $messageData = array(
                    "usuario" => array(
                        "userId" => $message['user_id'],
                        "nomeUser" => "Xossy",
                        "envio" => "14/04/2023 18:54",
                        "propia" => $message['proprio']
                    ),
                    "mensagem" =>$message['message'],
                    );

                $from->send(json_encode($messageData));
            }
        } else {
            $conversationId = $data->conversationId;
            $userId = $data->userId;
            $message = $data->message;

            // Forward the message to all conversation participants, except the sender
            foreach ($this->clients as $client) {
                if ($from !== $client) {

                    $messageData = array(
                        "usuario" => array(
                            "userId" => $userId,
                            "nomeUser" => "Wander",
                            "envio" => "14/04/2023 18:54",
                            "propia" => 1
                        ),
                        "mensagem" =>$message,
                    );
                    $client->send(json_encode($messageData));
                }
            }

            // Save the message to the database
            $stmt = $this->conexao->prepare("INSERT INTO mesagens (user_id, conversation_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $conversationId, $message]);

        }




    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function getOldMessages($conversationId,$isusuario)
    {


        $stmt = $this->conexao->prepare("SELECT conversation_id,user_id, message, 
                                         IF(user_id = :idusuario, 2, 1) as proprio
                                        FROM mesagens 
                                        WHERE conversation_id = :conversation_id 
                                        ORDER BY conversation_id ASC");
        $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindParam(':idusuario', $isusuario, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
