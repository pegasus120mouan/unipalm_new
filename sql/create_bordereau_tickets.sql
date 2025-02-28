CREATE TABLE `bordereau_tickets` (
  `id_bordereau_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `id_bordereau` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_bordereau_ticket`),
  KEY `fk_bordereau_tickets_bordereau` (`id_bordereau`),
  KEY `fk_bordereau_tickets_ticket` (`id_ticket`),
  CONSTRAINT `fk_bordereau_tickets_bordereau` FOREIGN KEY (`id_bordereau`) REFERENCES `bordereau` (`id_bordereau`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bordereau_tickets_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
