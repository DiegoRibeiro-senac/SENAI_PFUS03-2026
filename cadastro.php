<?php
session_start();
require('db/conexao.php');

// 1. Verificação de Acesso
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php?erro=Acesso negado.");
    exit;
}

$tipo = $_SESSION['usuario_tipo'];
$msg = '';

// 2. Processamento do Cadastro
if (isset($_POST['cadastro'])) {
    $nome  = LimpaPost($_POST['nome']);
    $email = LimpaPost($_POST['email']);
    $senha = LimpaPost($_POST['senha']);
    $tipo_novo = LimpaPost($_POST['tipo']);

    if (empty($nome) || empty($email) || empty($senha) || empty($tipo_novo)) {
        $msg = '<p style="color:red; font-weight:bold; text-align:center;">Preencha todos os campos!</p>';
    } else {
        // Verifica se o e-mail já existe
        $verificar = $conn->prepare("SELECT id_funcionario FROM funcionario WHERE email = ?");
        $verificar->execute([$email]);

        if ($verificar->rowCount() > 0) {
            $msg = '<p style="color:red; font-weight:bold; text-align:center;">E-mail já cadastrado!</p>';
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = $conn->prepare("INSERT INTO funcionario (nome, email, tipo, senha_hash) VALUES (?, ?, ?, ?)");

            if ($sql->execute([$nome, $email, $tipo_novo, $senha_hash])) {
                header("Location: cadastro.php?sucesso=1");
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - SENAI Linhares</title>
    <link rel="stylesheet" href="css/cadastro.css">
    <script src="js/jquery-3.7.1.min.js"></script>
</head>

<body>
    <div class="navbar">
        <img src="img/senai-logo-1.png" alt="SENAI">
        <ul>
            <?php if ($tipo == 'admin'): ?>
                <li><a href="relatorio.php">Relatórios</a></li>
                <li><a href="cadastro.php">Cadastrar</a></li>
                <li><a href="ambiente.php">Ambientes/salas</a></li>
            <?php else: ?>
                <li><a href="chamado.php">Abrir Chamado</a></li>
                <?= $tipo == 'manutencao' ? '<li><a href="funcionario.php">Chamados</a></li>' : '<li><a href="perfil.php">Perfil</a></li>' ?>
            <?php endif; ?>
            <li><a href="logout.php">Sair</a></li>
        </ul>
    </div>

    <div class="container-conteudo">
        <form class="formulario-cadastro" method="POST">
            <h2>Cadastro de Usuário</h2>

            <label class="rotulo">Nome completo: <span>*</span></label>
            <input type="text" name="nome" class="campo-entrada" placeholder="Nome completo" required>

            <label class="rotulo">Tipo de usuário: <span>*</span></label>
            <select name="tipo" class="campo-entrada" required>
                <option value="" hidden>Selecione</option>
                <option value="manutencao">Manutenção</option>
            </select>

            <label class="rotulo">Email: <span>*</span></label>
            <input type="email" name="email" class="campo-entrada" placeholder="seu@email.com" required>

            <label class="rotulo">Senha: <span>*</span></label>
            <input type="password" name="senha" class="campo-entrada" placeholder="Digite uma senha" required>

            <button class="botao-primario" type="submit" name="cadastro">Finalizar Cadastro</button>
            <?= $msg ?>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('sucesso')) {
                alert("Cadastro efetuado com sucesso!");
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>

</html>