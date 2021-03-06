<?php
function dbStructureTables() {
    $database =json_decode(file_get_contents('./database.json'), true);
    $i = 0;
    foreach($database[1] as $cle => $valeur):
        $tables[$i] = $cle;
        $i++;
    endforeach;
    $i = null;
    $database = null;
    return $tables;
}

function dbStructureColonnes() {
    $database =json_decode(file_get_contents('./database.json'), true);
    $i = 0;
    foreach($database[1] as $valeur):
        $colonnes[$i] = $valeur;
        $i++;
    endforeach;
    $i = null;
    $database = null;
    return $colonnes;
}

/* 
CREATE TABLE IF NOT EXITS utilisateurs(
    id int(11) AUTO_INCREMENT PRIMARY KEY,
    nom varchar(255) NOT NULL,
    prenoms varchar(255) NOT NULL,
    email varchar(255)  NOT NULL,
    keyword varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/

function explodeBy(array $nomColonnes) {
    for ($i=0; $i < count($nomColonnes); $i++) { 
        $colonnes[$i] = explode(',', $nomColonnes[$i]);
        for ($j=0; $j < count($colonnes[$i]); $j++) { 
            $colonnes[$i][$j] = explode(':', $colonnes[$i][$j]);
        }
    }
    return $colonnes;
}

function filtrage(string $nom, string $type, string $table) {
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
        $type = $type.', CONSTRAINT fk_'.trim($nom).'_'.$table.' 
            FOREIGN KEY('.trim($nom).') REFERENCES '.$tmp[1].'('.$tmp[0].')';
        $tmp = null;
    }
    return $nom.' '.$type;
}

function reqTable(array $colonne, string $table) {
    $requeteTable = "";
    for ($i=0; $i < count($colonne) - 1; $i++) {
        $requeteColonne = filtrage($colonne[$i][0], $colonne[$i][1], $table).',';
        $requeteTable = $requeteTable.''.$requeteColonne;
    }
    $requeteColonne = filtrage($colonne[count($colonne) - 1][0], $colonne[count($colonne) - 1][1], $table);
    $requeteTable = $requeteTable.''.$requeteColonne;
    return $requeteTable;
}

$tables = dbStructureTables();
$colonnes = dbStructureColonnes();
$colonnes = explodeBy($colonnes);

for ($i=0; $i < count($colonnes); $i++) {
    $requete = 'CREATE TABLE IF NOT EXISTS '.$tables[$i].'('
        .reqTable($colonnes[$i], $tables[$i]).') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';
    echo $requete;
    echo "\n";
}


/*
    class Utilisateurs extends Database {
        public function getAllUtilisateurs() {
            try {
                $databse = Database::db_connect();
                $demande = $database->query('SELECT id, nom, prenoms, email
                    FROM utilisateurs');
                $reponses = $demande->fetchAll(PDO::FETCH_ASSOC);
                $demande->closeCursor();
                return $reponses;
            }
            catch(PDOException $e) {
                print_r(json_encode([
                    'message' => "Erreur: nous n'avons pas pu obtenir TOUT UTILISATEURS. Merci !"
                ]));
            }
            $database = null;
        }
    }
*/
