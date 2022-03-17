<?php
class Run {
    public function __construct() {
        $database = json_decode(file_get_contents('./database.json'), true);
        $this->host = $database[0]['host'];
        $this->dbname = $database[0]['dbname'];
        $this->user = $database[0]['user'];
        $this->password = $database[0]['password'];
    }

    protected function serverConnect() {
        try {
            return new PDO("mysql: host=$this->host", $this->user, $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch(PDOException $e) {
            print_r(json_encode([
                'message' => "Erreur: la connexion au serveur n'est pas établie. Merci !".$e->getMessage()
            ], JSON_FORCE_OBJECT));
        }
    }

    public function data() {
        return $this->host;
    }

    public function createDatabase() {
        try {
            $server = $this->serverConnect();
            $server->exec("CREATE DATABASE $this->dbname");
            $server = null;
        }
        catch(PDOException $e) {
            print_r(json_encode([
                'message' => "Erreur: la base de données n'a pas être '$this->dbname'. Merci !"
            ], JSON_FORCE_OBJECT));
        }
    }
}

$lahatra = new Run;
$lahatra->data();
