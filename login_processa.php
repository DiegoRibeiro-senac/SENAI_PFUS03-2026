<?php
ob_start();
session_start();
require('db/conexao.php');

// 1. Coleta os dados do formulário
$email = $_POST['email'] ?? null;
$senha = $_POST['senha'] ?? '';

if ($email && $senha) {
    try {
        // 2. Busca o funcionário pelo e-mail
        $sql = $conn->prepare("SELECT id_funcionario, nome, tipo, senha_hash FROM funcionario WHERE email = ?");
        $sql->execute([$email]);
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);

        // 3. Verifica se existe e se a senha bate
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Sucesso! Criar a sessão
            $_SESSION['usuario_id'] = $usuario['id_funcionario'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            $_SESSION['logado'] = true;

            // 4. Redirecionar conforme o tipo (ENUM do seu BD)
            if ($usuario['tipo'] === 'admin') {
                header("Location: relatorio.php");
                exit;
            } elseif ($usuario['tipo'] === 'manutencao') {
                header("Location: chamado.php");
                exit;
            } else {
                header("Location: chamado.php");
                exit;
            }
            exit;
        } else {
            // Erro: dados incorretos
            header("Location: login.php?erro=Email ou senha inválidos");
            exit;
        }
    } catch (PDOException $e) {
        die("Erro no banco de dados: " . $e->getMessage());
    }
} else {
    header("Location: login.php?erro=Preencha todos os campos");
    exit;
}
