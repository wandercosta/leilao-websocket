-- Dados fictícios para demonstração. Nenhum dado real.
-- Senha de todos os usuários: demo1234

SET NAMES utf8mb4;

INSERT INTO usuarios (nome, email, senha_hash) VALUES
    ('Ana',   'ana@exemplo.com',   '$2y$12$4dAvs0hCEC4/6CLxczwQ8u/SJdzkhuPr6MkFKBSH65rNQFMoDFjuq'),
    ('Bruno', 'bruno@exemplo.com', '$2y$12$4dAvs0hCEC4/6CLxczwQ8u/SJdzkhuPr6MkFKBSH65rNQFMoDFjuq'),
    ('Carla', 'carla@exemplo.com', '$2y$12$4dAvs0hCEC4/6CLxczwQ8u/SJdzkhuPr6MkFKBSH65rNQFMoDFjuq');

-- As datas de encerramento são relativas ao momento em que o banco é criado.
INSERT INTO leiloes (titulo, descricao, imagem, lance_inicial, incremento_minimo, encerra_em) VALUES
    ('Bicicleta speed aro 700',
     'Quadro de alumínio tamanho 54, grupo de 16 velocidades, pouco uso. Retirada no local.',
     '/img/bicicleta.svg', 120000, 5000, DATE_ADD(NOW(), INTERVAL 3 DAY)),
    ('Câmera analógica 35 mm',
     'Corpo em metal com lente 50 mm f/1.8. Fotômetro funcionando, acompanha estojo.',
     '/img/camera.svg', 45000, 2000, DATE_ADD(NOW(), INTERVAL 2 HOUR)),
    ('Coleção de 40 discos de vinil',
     'Discos de MPB e samba dos anos 70 e 80, capas em bom estado.',
     '/img/vinil.svg', 80000, 2500, DATE_ADD(NOW(), INTERVAL 7 DAY)),
    ('Cafeteira expresso manual',
     'Cafeteira de alavanca com moedor. Lote já encerrado, para demonstrar a validação.',
     '/img/cafeteira.svg', 30000, 1000, DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO lances (leilao_id, usuario_id, valor, criado_em) VALUES
    (1, 2, 120000, DATE_SUB(NOW(), INTERVAL 50 MINUTE)),
    (1, 3, 125000, DATE_SUB(NOW(), INTERVAL 20 MINUTE)),
    (4, 1, 30000,  DATE_SUB(NOW(), INTERVAL 2 DAY)),
    (4, 2, 32000,  DATE_SUB(NOW(), INTERVAL 30 HOUR)),
    (4, 1, 35000,  DATE_SUB(NOW(), INTERVAL 26 HOUR));
