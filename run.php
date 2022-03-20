<?php
class Run {
    public function __construct() {
        $database = json_decode(file_get_contents('./database.json'), true);
        $this->databaseConnexion = $database[0];
        $this->host = $database[0]['host'];
        $this->dbname = $database[0]['dbname'];
        $this->user = $database[0]['user'];
        $this->password = $database[0]['password'];
        $i = 0;
        foreach($database[1] as $cle => $valeur) {
            $tables[$i] = $cle;
            $colonnes[$i] = $valeur;
            $i++;
        }
        $i = null;
        $database = null;
        $this->tables = $tables;
        $this->colonnes = $colonnes;
    }

    private function serverConnect() {
        try {
            return new PDO("mysql:host=$this->host; charset=utf8", $this->user, $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch(PDOException $e) {
            print_r(json_encode([
                'message' => "Erreur: la connexion au serveur n'est pas établie. Merci !".$e->getMessage()
            ], JSON_FORCE_OBJECT));
        }
    }

    private function databaseConnect() {
        try {
            return new PDO("mysql:host=$this->host; dbname=$this->dbname; charset=utf8", 
                $this->user, $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch(PDOException $e) {
            print_r(json_encode([
                'message' => "Erreur: la connexion à la base de données n'est pas établie. Merci !"
            ], JSON_FORCE_OBJECT));
        }
    }

    private function createDatabase() {
        try {
            $server = $this->serverConnect();
            $server->exec("CREATE DATABASE IF NOT EXISTS $this->dbname;");
            $server = null;
            echo ">>> La base de données est créée avec succès.\n";
            return 1;
        }
        catch(PDOException $e) {
            print_r(json_encode([
                'message' => "Erreur: la base de données n'a pas être '$this->dbname'. Merci !"
            ], JSON_FORCE_OBJECT));
        }
    }

    private function explodeColonnes() {
        $colonnes = $this->colonnes;
        for ($i=0; $i <count($colonnes) ; $i++) { 
            $colonnes[$i] = explode(',', $colonnes[$i]);
            for ($j=0; $j <count($colonnes[$i]) ; $j++) { 
                $colonnes[$i][$j] = explode(':', $colonnes[$i][$j]);
            }
        }
        return $colonnes;
    }

    private function filtrageConstraints(string $nom, string $type, string $table) {
        if(preg_match('#^\*#', trim($nom))) {
            $nom = str_replace('*', '', $nom);
            $type = $type. ' NOT NULL';
        }

        if(preg_match("#^_#", trim($nom))) {
            $nom = str_replace('_', '', $nom);
            $type = $type.' PRIMARY KEY';
        }

        if(preg_match('#\+\+$#', trim($nom))) {
            $nom = str_replace('+', '', $nom);
            $type = $type.' AUTO_INCREMENT';
        }
    
        if(preg_match('#^\##', trim($nom))) {
            $nom = str_replace('#', '', trim($nom));
            $tmp = explode('_', $nom);
            $type = $type.', CONSTRAINT fk_'.trim($nom).'_'.$table.' FOREIGN KEY('.trim($nom).') REFERENCES '.$tmp[1].'('.$tmp[0].')';
            $tmp = null;
        }
        return $nom.' '.$type;
    }
    
    private function generateColonnes(array $colonnes, string $table) {
        $myColonne = "";
        for ($i=0; $i < count($colonnes) - 1; $i++) { 
            $line = $this->filtrageConstraints($colonnes[$i][0], $colonnes[$i][1], $table).',';
            $myColonne = $myColonne.''.$line;
        }
        $line = $this->filtrageConstraints($colonnes[count($colonnes) - 1][0], $colonnes[count($colonnes) - 1][1], $table);
        return $myColonne.''.$line;
    }

    private function generateTables() {
        $colonnes = $this->explodeColonnes();
        $tables = $this->tables;
        $requete = "";
        for ($i=0; $i < count($tables); $i++) { 
            $res = "CREATE TABLE IF NOT EXISTS ".$tables[$i]."("
                .$this->generateColonnes($colonnes[$i], $tables[$i]).");";
            $requete = $requete."\n".$res;
        }
        return $requete;
    }

    public function createDatabaseProject() {
        try {
            $createDB = $this->createDatabase();
            if($createDB === 1) {
                $database=$this->databaseConnect();
                $database->exec("USE $this->dbname;");
                $database->exec($this->generateTables());
                $database = null;
                echo ">>> Les tables sont créées avec succès. Merci !";
            }
            else {
                throw new Exception("création de la base de données !");
                exit;
            }
        }
        catch(PDOException $e) {
            print_r(json_encode([
                'message' => "Erreur: ".$e->getMessage()
            ], JSON_FORCE_OBJECT));
        }
    }

    private function jsonDBConnexion() {
        return json_encode($this->databaseConnexion, JSON_FORCE_OBJECT);
    }

    private function writeFile($fichierPath, $texte) {
        $fichierConnectDB = fopen($fichierPath, "w");
        fwrite($fichierConnectDB, $texte);
        fclose($fichierConnectDB);
    }

    public function createProject() {
        try {
            if(!is_dir('./api-'.$this->dbname)) {
                mkdir('./api-'.$this->dbname.'/models', 0777, true);
                mkdir('./api-'.$this->dbname.'/controllers', 0777, true);
                $this->writeFile("./api-$this->dbname/models/db.json", $this->jsonDBConnexion());
                $this->writeFile("./api-$this->dbname/.gitignore", "/models/db.json");
            }
        }
        catch(PDOException $e) {
            print_r(json_encode([
                'message' => "Erreur: ".$e->getMessage()
            ], JSON_FORCE_OBJECT));
        }
    }
}

$lahatra = new Run;
$lahatra->createDatabaseProject();
echo "\n";
$lahatra->createProject();
// echo $lahatra->jsonDBConnexion();
echo "\n";
