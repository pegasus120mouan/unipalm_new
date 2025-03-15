ALTER TABLE `recus_demandes`
ADD COLUMN `numero_recu` varchar(50) NOT NULL AFTER `id`,
ADD UNIQUE KEY `numero_recu` (`numero_recu`),
MODIFY COLUMN `id` int(11) NOT NULL AUTO_INCREMENT,
ADD PRIMARY KEY (`id`);
