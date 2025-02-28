-- Ajout de la clé primaire
ALTER TABLE `financement` ADD PRIMARY KEY (`Numero_financement`);

-- Ajout de la clé étrangère
ALTER TABLE `financement` ADD CONSTRAINT `financement_ibfk_1` 
FOREIGN KEY (`id_agent`) REFERENCES `agents` (`id_agent`) 
ON DELETE CASCADE ON UPDATE CASCADE;
