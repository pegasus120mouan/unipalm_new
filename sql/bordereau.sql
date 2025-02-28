-- Structure de la table `bordereau`
CREATE TABLE `bordereau` (
  `id_bordereau` int(11) NOT NULL AUTO_INCREMENT,
  `numero_bordereau` varchar(50) NOT NULL,
  `id_agent` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `poids_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `date_validation_boss` datetime DEFAULT NULL,
  `montant_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `montant_payer` decimal(10,2) DEFAULT NULL,
  `montant_reste` decimal(10,2) DEFAULT NULL,
  `date_paie` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut_bordereau` enum('soldé','non soldé') NOT NULL DEFAULT 'non soldé',
  PRIMARY KEY (`id_bordereau`),
  UNIQUE KEY `numero_bordereau` (`numero_bordereau`),
  KEY `id_agent` (`id_agent`),
  CONSTRAINT `bordereau_ibfk_1` FOREIGN KEY (`id_agent`) REFERENCES `agents` (`id_agent`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Trigger pour mettre à jour montant_reste et statut_bordereau
DELIMITER //
CREATE TRIGGER `update_bordereau_after_payment` BEFORE UPDATE ON `bordereau`
FOR EACH ROW
BEGIN
    -- Calculer le montant restant
    SET NEW.montant_reste = NEW.montant_total - COALESCE(NEW.montant_payer, 0);
    
    -- Mettre à jour le statut en fonction du montant restant
    IF NEW.montant_reste <= 0 THEN
        SET NEW.statut_bordereau = 'soldé';
    ELSE
        SET NEW.statut_bordereau = 'non soldé';
    END IF;
    
    -- Mettre à jour la date de paiement si un nouveau paiement est effectué
    IF NEW.montant_payer IS NOT NULL AND NEW.montant_payer > COALESCE(OLD.montant_payer, 0) THEN
        SET NEW.date_paie = NOW();
    END IF;
END //
DELIMITER ;
