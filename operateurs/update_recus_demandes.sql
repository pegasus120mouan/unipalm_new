-- Add numero_recu and date_demande columns if they don't exist
ALTER TABLE `recus_demandes`
ADD COLUMN IF NOT EXISTS `numero_recu` varchar(50) NOT NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `date_demande` datetime NOT NULL AFTER `date_paiement`,
MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT,
ADD PRIMARY KEY IF NOT EXISTS (`id`);

-- Update existing records with a generated numero_recu
UPDATE `recus_demandes` 
SET `numero_recu` = CONCAT('DEM-', DATE_FORMAT(date_paiement, '%Y%m%d'), LPAD(id, 4, '0'))
WHERE `numero_recu` IS NULL OR `numero_recu` = '';

-- Add constraints
ALTER TABLE `recus_demandes`
ADD UNIQUE KEY IF NOT EXISTS `numero_recu` (`numero_recu`),
ADD CONSTRAINT IF NOT EXISTS `fk_recus_demandes_demande` FOREIGN KEY (`demande_id`) REFERENCES `demande_sortie` (`id_demande`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT IF NOT EXISTS `fk_recus_demandes_caissier` FOREIGN KEY (`caissier_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
