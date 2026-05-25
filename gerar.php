<?php
require('db/conexao.php');

$nome = "Usuario Teste";
$email = "teste@senai.com";
$senha_pura = "123";
$tipo = "funcionario";

// Gera o hash correto
$hash = password_hash($senha_pura, PASSWORD_DEFAULT);

try {
    $sql = $conn->prepare("INSERT INTO funcionario (nome, email, tipo, senha_hash) VALUES (?, ?, ?, ?)");
    $sql->execute([$nome, $email, $tipo, $hash]);
    echo "Usuário criado com sucesso! <br> Email: $email <br> Senha: $senha_pura";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

$nome = "Usuario Testes";
$email = "teste2@senai.com";
$senha_pura = "123";
$tipo = "manutencao";

// Gera o hash correto
$hash = password_hash($senha_pura, PASSWORD_DEFAULT);

try {
    $sql = $conn->prepare("INSERT INTO funcionario (nome, email, tipo, senha_hash) VALUES (?, ?, ?, ?)");
    $sql->execute([$nome, $email, $tipo, $hash]);
    echo "Usuário criado com sucesso! <br> Email: $email <br> Senha: $senha_pura";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

$nome = "Usuario Testess";
$email = "teste3@senai.com";
$senha_pura = "123";
$tipo = "admin";

// Gera o hash correto
$hash = password_hash($senha_pura, PASSWORD_DEFAULT);

try {
    $sql = $conn->prepare("INSERT INTO funcionario (nome, email, tipo, senha_hash) VALUES (?, ?, ?, ?)");
    $sql->execute([$nome, $email, $tipo, $hash]);
    echo "Usuário criado com sucesso! <br> Email: $email <br> Senha: $senha_pura";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}

header("Location: login.php");
