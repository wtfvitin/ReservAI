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
  email_restaurante VARCHAR(100),
  horario_abertura TIME NOT NULL,
  horario_fechamento TIME NOT NULL,
  cep_res VARCHAR(9) NOT NULL,
  endereco_rua_res VARCHAR(100) NOT NULL,
  endereco_num_res INT(4) NOT NULL,
  endereco_bairro_res VARCHAR(100) NOT NULL,
  endereco_cidade_res VARCHAR(100) NOT NULL,
  endereco_estado_res CHAR(2) NOT NULL,

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
  PRIMARY KEY (idcliente),
  UNIQUE KEY cpf_usuario_UNIQUE (cpf_cli),
  UNIQUE KEY email_usuario_UNIQUE (email_cli)
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

-- Pagamentos (relacionados à reserva)
CREATE TABLE IF NOT EXISTS pagamentos (
  idpagamento INT NOT NULL AUTO_INCREMENT,
  reserva_id INT NOT NULL,
  valor DECIMAL(10,2) NOT NULL,
  metodo_pagamento VARCHAR(50) NOT NULL,
  data_pagamento DATE NOT NULL,
  status_pagamento VARCHAR(30) DEFAULT 'pendente',
  PRIMARY KEY (idpagamento),
  INDEX idx_pagamento_reserva (reserva_id),
  CONSTRAINT fk_pagamento_reserva FOREIGN KEY (reserva_id)
    REFERENCES reservas (idreserva)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Funcionários
CREATE TABLE IF NOT EXISTS funcionarios (
  idfuncionario INT NOT NULL AUTO_INCREMENT,
  restaurante_id INT NOT NULL,
  nome_funcionario VARCHAR(100) NOT NULL,
  cargo VARCHAR(50) NOT NULL,
  telefone CHAR(12),
  email VARCHAR(100) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  PRIMARY KEY (idfuncionario),
  UNIQUE KEY email_UNIQUE (email),
  INDEX idx_func_restaurante (restaurante_id),
  CONSTRAINT fk_funcionario_restaurante FOREIGN KEY (restaurante_id)
    REFERENCES restaurantes (idrestaurante)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Categorias culinárias (por restaurante)
CREATE TABLE IF NOT EXISTS categorias_culinaria (
  idcategoria INT NOT NULL AUTO_INCREMENT,
  restaurante_id INT NOT NULL,
  nome VARCHAR(100) NOT NULL,
  PRIMARY KEY (idcategoria),
  INDEX idx_categoria_restaurante (restaurante_id),
  CONSTRAINT fk_categoria_restaurante FOREIGN KEY (restaurante_id)
    REFERENCES restaurantes (idrestaurante)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Cardápio (itens)
CREATE TABLE IF NOT EXISTS cardapio (
  idcardapio INT NOT NULL AUTO_INCREMENT,
  restaurante_id INT NOT NULL,
  categoria_id INT NULL,
  nome_alimento VARCHAR(100) NOT NULL,
  descricao TEXT NULL,
  preco DECIMAL(8,2) NULL,
  PRIMARY KEY (idcardapio),
  INDEX idx_cardapio_restaurante (restaurante_id),
  INDEX idx_cardapio_categoria (categoria_id),
  CONSTRAINT fk_cardapio_restaurante FOREIGN KEY (restaurante_id)
    REFERENCES restaurantes (idrestaurante)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_cardapio_categoria FOREIGN KEY (categoria_id)
    REFERENCES categorias_culinaria (idcategoria)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Avaliações
CREATE TABLE IF NOT EXISTS avaliacoes (
  idavaliacao INT NOT NULL AUTO_INCREMENT,
  cliente_id INT NOT NULL,
  restaurante_id INT NOT NULL,
  nota TINYINT NOT NULL,
  comentario TEXT NULL,
  data_avaliacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (idavaliacao),
  INDEX idx_avaliacao_cliente (cliente_id),
  INDEX idx_avaliacao_restaurante (restaurante_id),
  CONSTRAINT fk_avaliacao_cliente FOREIGN KEY (cliente_id)
    REFERENCES clientes (idcliente)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_avaliacao_restaurante FOREIGN KEY (restaurante_id)
    REFERENCES restaurantes (idrestaurante)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Tabelas auxiliares ou de versão (opcionais) podem ser adicionadas abaixo

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

USE reservai;
select*from clientes;

INSERT INTO reservas (cliente_id, numero_clientes, mesa_id, restaurante_id, data_reserva, horario_inicio, horario_fim)
VALUES (1, 6, 1, 1, '2025-02-20', '19:00:00', '20:30:00');









