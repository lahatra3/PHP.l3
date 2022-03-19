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

function filtrage(string $nom, string $type) {
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
        $type = $type.', CONSTRAINT fk_'.trim($nom).'_lahatra 
            FOREIGN KEY('.trim($nom).') REFERENCES '.$tmp[1].'('.$tmp[0].')';
        $tmp = null;
    }
    return $nom.' '.$type;
}

function reqTable(array $colonne) {
    $requeteTable = "";
    for ($i=0; $i < count($colonne) - 1; $i++) {
        $requeteColonne = filtrage($colonne[$i][0], $colonne[$i][1]).',';
        $requeteTable = $requeteTable.''.$requeteColonne;
    }
    $requeteColonne = filtrage($colonne[count($colonne) - 1][0], $colonne[count($colonne) - 1][1]);
    $requeteTable = $requeteTable.''.$requeteColonne;
    return $requeteTable;
}

$colonnes = dbStructureColonnes();
$colonnes = explodeBy($colonnes);

for ($i=0; $i < count($colonnes); $i++) { 
    echo reqTable($colonnes[$i]);
    echo "\n";
}
