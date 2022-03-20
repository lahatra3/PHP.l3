# PHP.l3
  J'étudie et j'évolue aussi...!
  Mercredi 16 Mars 2022

  #### Lecture de *database.json*:
  - `*...` : NOT NULL,
  - `_...` : PRIMARY KEY,
  - `...++` : AUTO_INCREMENT,
  - `...#` : FOREIGN KEY

  **<u>Remarques:</u>**
  Les ordres de des clés sont importants sur les contraintes, et il faut les respecter...!
  
    - *Exemples:*
      - *nom : nom NOT NULL,
      - _id : id PRIMARY KEY,
      - id : id AUTO_INCREMENT,
      - *_id : id NOT NULL PRIMARY KEY,
      - *_id++ : id NOT NULL AUTO_INCREMENT PRIMARY KEY,
      - #id_utilisateurs : CONSTRAINT fk_id_utilisateurs_(nom de la table) FOREING KEY id_utilisateurs REFERENCES utilisateurs(id)


😊**PHP.lahatra3**🤓
