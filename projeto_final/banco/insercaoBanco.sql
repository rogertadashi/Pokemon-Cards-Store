-- ⚙️ Dados de exemplo para popular o banco conforme seu schema
-- Copie e cole no phpMyAdmin. Rode UMA VEZ.
-- Requer que o schema já tenha sido criado (CREATE DATABASE/TABLES).
USE pokestore;
SET NAMES utf8mb4;

START TRANSACTION;

-- =========================================================
-- 1) USUÁRIOS (usa ON DUPLICATE para não quebrar se já existir)
-- =========================================================
INSERT INTO usuarios (nome, login, senha, funcao) VALUES
  ('Líder',      'elite4',  'admon123', 'Administrador')
ON DUPLICATE KEY UPDATE
  nome=VALUES(nome), senha=VALUES(senha), funcao=VALUES(funcao);

INSERT INTO usuarios (nome, login, senha, funcao) VALUES
  ('Vendedor 1', 'seller1', '123456',   'Vendedor'),
  ('Admin 2',    'admin2',  'admin456', 'Administrador')
ON DUPLICATE KEY UPDATE
  nome=VALUES(nome), senha=VALUES(senha), funcao=VALUES(funcao);

-- =========================================================
-- 2) CLIENTES
-- =========================================================
INSERT INTO clientes (nome, email, telefone, cpf) VALUES
('João Silva',     'joao@example.com',  '(11) 90000-0001', '111.111.111-11'),
('Maria Souza',    'maria@example.com', '(11) 90000-0002', '222.222.222-22'),
('Carlos Lima',    'carlos@example.com','(11) 90000-0003', '333.333.333-33'),
('Ana Pereira',    'ana@example.com',   '(11) 90000-0004', '444.444.444-44'),
('Bruno Costa',    'bruno@example.com', '(11) 90000-0005', '555.555.555-55');

-- =========================================================
-- 3) CARTAS (Produtos) – códigos únicos (usa ON DUPLICATE)
-- =========================================================
INSERT INTO cartas (codigo, nome, tipo, raridade, valor, imagem) VALUES
  ('PK001','Bulbasaur', 'Planta', 'Comum',       26.90, '/img/001-bulbasaur.jpg'),
  ('PK002','Ivysaur',   'Planta', 'Incomum',     45.00, '/img/002-ivysaur.jpg'),
  ('PK003','Venusaur',  'Planta', 'Rara',        99.90, '/img/003-venusaur.jpg'),
  ('PK004','Charmander','Fogo',   'Comum',       29.90, '/img/004-charmander.jpg'),
  ('PK005','Charmeleon','Fogo',   'Incomum',     49.90, '/img/005-charmeleon.jpg'),
  ('PK006','Charizard', 'Fogo',   'Ultra Rara', 149.90, '/img/006-charizard.jpg'),
  ('PK007','Squirtle',  'Água',   'Comum',       27.50, '/img/007-squirtle.jpg'),
  ('PK008','Wartortle', 'Água',   'Incomum',     44.90, '/img/008-wartortle.jpg'),
  ('PK009','Blastoise', 'Água',   'Rara',       129.90, '/img/009-blastoise.jpg')
ON DUPLICATE KEY UPDATE
  nome=VALUES(nome),
  tipo=VALUES(tipo),
  raridade=VALUES(raridade),
  valor=VALUES(valor),
  imagem=VALUES(imagem);

-- =========================================================
-- 4) VENDAS (cabeçalho) – IDs explícitos para facilitar referência
--    cliente_id e usuario_id referenciados por subselect
--    valor_total já vem calculado (conferir itens abaixo)
-- =========================================================
INSERT INTO vendas (id, cliente_id, usuario_id, condicao_pagamento, valor_total)
VALUES
  (1001,
    (SELECT id FROM clientes  WHERE nome='João Silva'  LIMIT 1),
    (SELECT id FROM usuarios  WHERE login='elite4'     LIMIT 1),
    'À vista', 99.70),
  (1002,
    (SELECT id FROM clientes  WHERE nome='Maria Souza' LIMIT 1),
    (SELECT id FROM usuarios  WHERE login='seller1'    LIMIT 1),
    'Pix',     129.80),
  (1003,
    (SELECT id FROM clientes  WHERE nome='Carlos Lima' LIMIT 1),
    (SELECT id FROM usuarios  WHERE login='seller1'    LIMIT 1),
    'Cartão de crédito', 109.60),
  (1004,
    (SELECT id FROM clientes  WHERE nome='Ana Pereira' LIMIT 1),
    (SELECT id FROM usuarios  WHERE login='admin2'     LIMIT 1),
    'Parcelado', 203.70),
  (1005,
    (SELECT id FROM clientes  WHERE nome='Bruno Costa' LIMIT 1),
    (SELECT id FROM usuarios  WHERE login='seller1'    LIMIT 1),
    'Cartão de débito', 59.00);

-- =========================================================
-- 5) ITENS DA VENDA – usa subselect para pegar carta_id e valor atual
--    VENDA 1001: 2× PK001 (29.90) + 1× PK004 (39.90) = 99.70
-- =========================================================
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1001, id, 2, valor FROM cartas WHERE codigo='PK001' LIMIT 1;
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1001, id, 1, valor FROM cartas WHERE codigo='PK004' LIMIT 1;

-- VENDA 1002: 1× PK009 (79.90) + 1× PK010 (49.90) = 129.80
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1002, id, 1, valor FROM cartas WHERE codigo='PK009' LIMIT 1;
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1002, id, 1, valor FROM cartas WHERE codigo='PK010' LIMIT 1;

-- VENDA 1003: 3× PK005 (24.90) + 1× PK006 (34.90) = 109.60
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1003, id, 3, valor FROM cartas WHERE codigo='PK005' LIMIT 1;
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1003, id, 1, valor FROM cartas WHERE codigo='PK006' LIMIT 1;

-- VENDA 1004: 1× PK011 (149.90) + 2× PK003 (26.90) = 203.70
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1004, id, 1, valor FROM cartas WHERE codigo='PK011' LIMIT 1;
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1004, id, 2, valor FROM cartas WHERE codigo='PK003' LIMIT 1;

-- VENDA 1005: 1× PK002 (27.50) + 1× PK007 (31.50) = 59.00
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1005, id, 1, valor FROM cartas WHERE codigo='PK002' LIMIT 1;
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT 1005, id, 1, valor FROM cartas WHERE codigo='PK007' LIMIT 1;

COMMIT;








