-- Script reorganizado e com lógica corrigida
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE SCHEMA IF NOT EXISTS `reservai` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `reservai`;

-- Restaurantes
CREATE TABLE IF NOT EXISTS restaurantes (
  idrestaurante INT NOT NULL AUTO_INCREMENT,
  nome_restaurante VARCHAR(100) NOT NULL,
  telefone CHAR(12) NOT NULL,
  email_restaurante VARCHAR(100) NOT NULL,
  senha VARCHAR (100) NOT NULL,
  horario_abertura TIME NOT NULL,
  horario_fechamento TIME NOT NULL,
  cep_res VARCHAR(9) NOT NULL,
  endereco_rua_res VARCHAR(100) NOT NULL,
  endereco_num_res INT(4) NOT NULL,
  endereco_bairro_res VARCHAR(100) NOT NULL,
  endereco_cidade_res VARCHAR(100) NOT NULL,
  endereco_estado_res CHAR(2) NOT NULL,
  descricao TEXT NULL,
  logo_res LONGBLOB NULL,
  fotoPrincipal_res LONGBLOB NULL,
  foto1_res LONGBLOB NULL,
  foto2_res LONGBLOB NULL,
  foto3_res LONGBLOB	 NULL,
  PRIMARY KEY (idrestaurante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mesas
CREATE TABLE IF NOT EXISTS mesas (
  idmesa INT NOT NULL AUTO_INCREMENT,
  restaurante_id INT NOT NULL,
  numero INT NOT NULL,
  lugares INT NOT NULL,
  PRIMARY KEY (idmesa),
  INDEX idx_mesas_restaurante (restaurante_id),
  CONSTRAINT fk_mesas_restaurante FOREIGN KEY (restaurante_id)
    REFERENCES restaurantes (idrestaurante)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Clientes
CREATE TABLE IF NOT EXISTS clientes (
  idcliente INT NOT NULL AUTO_INCREMENT,
  nome_cli VARCHAR(45) NOT NULL,
  sobrenome_cli VARCHAR(90) NOT NULL,
  cpf_cli CHAR(11) NOT NULL UNIQUE,
  telefone_cli CHAR(11) NOT NULL,
  email_cli VARCHAR(100) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  foto_perfil MEDIUMBLOB,
  dtNasc_cli DATE NOT NULL,
  cep_cli VARCHAR(9) NOT NULL,
  endereco_rua_cli VARCHAR(100) NOT NULL,
  endereco_cidade_cli VARCHAR(100) NOT NULL,
  endereco_estado_cli CHAR(2) NOT NULL,
  PRIMARY KEY (idcliente)
) ENGINE=InnoDB;

-- Reservas
CREATE TABLE IF NOT EXISTS reservas (
  idreserva INT NOT NULL AUTO_INCREMENT,
  cliente_id INT NOT NULL,
  numero_clientes INT NOT NULL,
  mesa_id INT NULL,
  restaurante_id INT NULL,
  foto_restaurante MEDIUMBLOB,
  data_reserva DATE NOT NULL,
  horario_inicio TIME NOT NULL,
  horario_fim TIME NOT NULL,
  status VARCHAR(30) DEFAULT 'confirmada',
  PRIMARY KEY (idreserva),
  INDEX idx_reserva_cliente (cliente_id),
  INDEX idx_reserva_mesa (mesa_id),
  INDEX idx_reserva_restaurante (restaurante_id),
  CONSTRAINT fk_reserva_cliente FOREIGN KEY (cliente_id)
    REFERENCES clientes (idcliente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_reserva_mesa FOREIGN KEY (mesa_id)
    REFERENCES mesas (idmesa)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT fk_reserva_restaurante FOREIGN KEY (restaurante_id)
    REFERENCES restaurantes (idrestaurante)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Cardápio (itens)
CREATE TABLE IF NOT EXISTS cardapio (
  idcardapio INT NOT NULL AUTO_INCREMENT,
  restaurante_id INT NOT NULL,
  nome_alimento VARCHAR(100) NOT NULL,
  foto_alimento LONGBLOB NOT NULL,
  preco DECIMAL(8,2) NOT NULL,
  PRIMARY KEY (idcardapio),
  INDEX idx_cardapio_restaurante (restaurante_id),
  CONSTRAINT fk_cardapio_restaurante FOREIGN KEY (restaurante_id)
    REFERENCES restaurantes (idrestaurante)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

select * from restaurantes;

select * from cardapio;


