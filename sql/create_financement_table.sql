CREATE TABLE `financement` (
  `Numero_financement` int(11) NOT NULL,
  `id_agent` int(11) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `motif` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
