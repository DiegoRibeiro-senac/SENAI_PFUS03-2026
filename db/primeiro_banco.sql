CREATE DATABASE IF NOT EXISTS tcc;
USE tcc;

CREATE TABLE ambiente (
    id_ambiente INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE sala (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    id_ambiente INT NOT NULL,
    num_sala CHAR(2) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_ambiente) REFERENCES ambiente(id_ambiente)
);

CREATE TABLE funcionario (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    tipo ENUM('manutencao', 'funcionario', 'admin') NOT NULL,
    senha_hash VARCHAR(255) NOT NULL
);

CREATE TABLE solicitacao (
    id_solicitacao INT AUTO_INCREMENT PRIMARY KEY,
    id_ambiente INT NOT NULL,
    id_sala INT NOT NULL,
    id_funcionario INT,
    id_funcionario_cancelou INT,
    id_funcionario_concluiu INT,
    motivo VARCHAR(150) NOT NULL,
    foto VARCHAR(255),
    prioridade ENUM('Baixa', 'Média', 'Alta', 'Urgente') NOT NULL DEFAULT 'Baixa',
    motivo_cancelamento TEXT,
    data_cancelamento DATETIME,
    data_inic DATETIME,
    data_fim DATETIME,
    status ENUM('solicitado','andamento','concluido','cancelado','recusado') NOT NULL DEFAULT 'solicitado',
    FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario),
    FOREIGN KEY (id_sala) REFERENCES sala(id_sala),
    FOREIGN KEY (id_ambiente) REFERENCES ambiente(id_ambiente)
);

-- INSERTS DE AMBIENTE
INSERT INTO ambiente (nome) VALUES
('Pedagógico'), ('Marcenaria'), ('Laboratórios'), ('Salas');

-- INSERTS DE SALA
INSERT INTO sala (id_ambiente, num_sala, nome) VALUES
(1, '01', 'Sala de Aula 01'), (1, '02', 'Sala de Aula 02'),
(1, '03', 'Sala de Aula 03'), (1, '04', 'Sala de Aula 04'),
(2, '01', 'Oficina de Marcenaria'), (2, '02', 'Depósito de Madeira'),
(3, '01', 'Laboratório de Informática'), (3, '02', 'Laboratório de Redes'),
(3, '03', 'Laboratório de Elétrica'), (4, '01', 'Sala dos Professores'),
(4, '02', 'Sala de Reuniões');

-- INSERTS DE SOLICITAÇÃO (Ajustados para IDs existentes e nomes de prioridade corretos)
INSERT INTO solicitacao (id_ambiente, id_sala, motivo, status, prioridade) VALUES
(1, 1, 'Ar-condicionado não está gelando', 'solicitado', 'Alta'),
(3, 7, '3 PCs não estão ligando', 'solicitado', 'Urgente'),
(1, 2, 'Projetor com defeito', 'andamento', 'Média'),
(4, 10, 'Mesa quebrada na sala dos professores', 'concluido', 'Baixa');