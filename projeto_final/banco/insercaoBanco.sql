-- =========================================================
-- ⚙️ SCRIPT DE POPULAÇÃO DO BANCO pokestore
-- =========================================================
USE pokestore;
SET NAMES utf8mb4;
START TRANSACTION;

-- =========================================================
-- 1️⃣ USUÁRIOS (Admin / Vendedor — controle de estoque)
-- =========================================================
INSERT INTO usuarios (nome, login, senha, funcao)
VALUES 
  ('Líder',      'elite4',  'admon123', 'Administrador'),
  ('Vendedor 1', 'seller1', '123456',   'Vendedor'),
  ('Admin 2',    'admin2',  'admin456', 'Administrador')
ON DUPLICATE KEY UPDATE 
  nome = VALUES(nome),
  senha = VALUES(senha),
  funcao = VALUES(funcao);

-- =========================================================
-- 2️⃣ CLIENTES (quem faz as compras)
-- =========================================================
INSERT INTO clientes (nome, email, telefone, cpf, login, senha)
VALUES
('João Silva', 'joao@example.com', '(11) 90000-0001', '111.111.111-11', 'joaos', '123456'),
('Maria Souza', 'maria@example.com', '(11) 90000-0002', '222.222.222-22', 'marias', '123456'),
('Carlos Lima', 'carlos@example.com', '(11) 90000-0003', '333.333.333-33', 'carlosl', '123456'),
('Ana Pereira', 'ana@example.com', '(11) 90000-0004', '444.444.444-44', 'anap', '123456'),
('Bruno Costa', 'bruno@example.com', '(11) 90000-0005', '555.555.555-55', 'brunoc', '123456')
ON DUPLICATE KEY UPDATE
  nome = VALUES(nome),
  telefone = VALUES(telefone);

-- =========================================================
-- 3️⃣ CARTAS (com estoque)
-- =========================================================
INSERT INTO cartas (codigo, nome, tipo, raridade, valor, estoque, imagem)
VALUES
  ('PK001','Bulbasaur',  'Planta',   'Comum',       26.90, 30, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/1.png'),
  ('PK002','Ivysaur',    'Planta',   'Incomum',     45.00, 25, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/2.png'),
  ('PK003','Venusaur',   'Planta',   'Rara',        99.90, 20, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/3.png'),
  ('PK004','Charmander', 'Fogo',     'Comum',       29.90, 40, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/4.png'),
  ('PK005','Charmeleon', 'Fogo',     'Incomum',     49.90, 35, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/5.png'),
  ('PK006','Charizard',  'Fogo',     'Ultra Rara', 149.90, 10, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/6.png'),
  ('PK007','Squirtle',   'Água',     'Comum',       27.50, 50, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/7.png'),
  ('PK008','Wartortle',  'Água',     'Incomum',     44.90, 25, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/8.png'),
  ('PK009','Blastoise',  'Água',     'Rara',       129.90, 15, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/9.png'),
  ('PK010','Caterpie',   'Planta',   'Comum',       19.90, 60, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/10.png'),
  ('PK011','Pikachu',    'Elétrico', 'Ultra Rara', 149.90, 12, 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png')
ON DUPLICATE KEY UPDATE
  nome = VALUES(nome),
  tipo = VALUES(tipo),
  raridade = VALUES(raridade),
  valor = VALUES(valor),
  estoque = VALUES(estoque),
  imagem = VALUES(imagem);


-- =========================================================
-- 4️⃣ VENDAS (somente clientes)
-- =========================================================
INSERT INTO vendas (cliente_id, condicao_pagamento, valor_total)
SELECT c.id, 'À vista', 99.70
  FROM clientes c WHERE c.nome='João Silva' LIMIT 1;

INSERT INTO vendas (cliente_id, condicao_pagamento, valor_total)
SELECT c.id, 'Pix', 129.80
  FROM clientes c WHERE c.nome='Maria Souza' LIMIT 1;

INSERT INTO vendas (cliente_id, condicao_pagamento, valor_total)
SELECT c.id, 'Cartão de crédito', 109.60
  FROM clientes c WHERE c.nome='Carlos Lima' LIMIT 1;

INSERT INTO vendas (cliente_id, condicao_pagamento, valor_total)
SELECT c.id, 'Parcelado', 203.70
  FROM clientes c WHERE c.nome='Ana Pereira' LIMIT 1;

INSERT INTO vendas (cliente_id, condicao_pagamento, valor_total)
SELECT c.id, 'Cartão de débito', 59.00
  FROM clientes c WHERE c.nome='Bruno Costa' LIMIT 1;

-- =========================================================
-- 5️⃣ ITENS DAS VENDAS + atualização de estoque
-- =========================================================
-- VENDA 1: 2× PK001 + 1× PK004
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 2, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=99.70 AND c.codigo='PK001' LIMIT 1;
UPDATE cartas SET estoque = estoque - 2 WHERE codigo='PK001';

INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 1, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=99.70 AND c.codigo='PK004' LIMIT 1;
UPDATE cartas SET estoque = estoque - 1 WHERE codigo='PK004';

-- VENDA 2: 1× PK009 + 1× PK010
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 1, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=129.80 AND c.codigo='PK009' LIMIT 1;
UPDATE cartas SET estoque = estoque - 1 WHERE codigo='PK009';

INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 1, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=129.80 AND c.codigo='PK010' LIMIT 1;
UPDATE cartas SET estoque = estoque - 1 WHERE codigo='PK010';

-- VENDA 3: 3× PK005 + 1× PK006
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 3, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=109.60 AND c.codigo='PK005' LIMIT 1;
UPDATE cartas SET estoque = estoque - 3 WHERE codigo='PK005';

INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 1, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=109.60 AND c.codigo='PK006' LIMIT 1;
UPDATE cartas SET estoque = estoque - 1 WHERE codigo='PK006';

-- VENDA 4: 1× PK011 + 2× PK003
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 1, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=203.70 AND c.codigo='PK011' LIMIT 1;
UPDATE cartas SET estoque = estoque - 1 WHERE codigo='PK011';

INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 2, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=203.70 AND c.codigo='PK003' LIMIT 1;
UPDATE cartas SET estoque = estoque - 2 WHERE codigo='PK003';

-- VENDA 5: 1× PK002 + 1× PK007
INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 1, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=59.00 AND c.codigo='PK002' LIMIT 1;
UPDATE cartas SET estoque = estoque - 1 WHERE codigo='PK002';

INSERT INTO itens_venda (venda_id, carta_id, quantidade, valor_unitario)
SELECT v.id, c.id, 1, c.valor
  FROM vendas v JOIN cartas c
  WHERE v.valor_total=59.00 AND c.codigo='PK007' LIMIT 1;
UPDATE cartas SET estoque = estoque - 1 WHERE codigo='PK007';

COMMIT;
