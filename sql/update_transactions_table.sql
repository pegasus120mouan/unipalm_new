ALTER TABLE transactions 
ADD COLUMN id_ticket INT NULL,
ADD COLUMN id_bordereau INT NULL,
ADD FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket),
ADD FOREIGN KEY (id_bordereau) REFERENCES bordereau(id_bordereau);
