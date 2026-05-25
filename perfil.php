<?php
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Se não estiver logado, manda de volta para o login
    header("Location: login.php?erro=Acesso negado. Faça login primeiro.");
    exit;
}

$idFuncionarioLogado = $_SESSION['usuario_id'];
$tipo = $_SESSION['usuario_tipo'];
?>

<?php

require("db/conexao.php");

$sql = $conn->prepare("
    SELECT 
        s.motivo,
        s.status,
        s.prioridade,
        s.data_inic,
        s.data_fim,
        a.nome AS ambiente_nome,
        sa.nome AS sala_nome,
        sa.num_sala,
        f.nome AS funcionario_nome
    FROM solicitacao s
    JOIN ambiente a ON s.id_ambiente = a.id_ambiente
    JOIN sala sa ON s.id_sala = sa.id_sala
    LEFT JOIN funcionario f ON s.id_funcionario = f.id_funcionario
    WHERE s.id_funcionario = :id_funcionario
    ORDER BY s.id_solicitacao DESC
");
$sql->bindValue(':id_funcionario', $idFuncionarioLogado, PDO::PARAM_INT);
$sql->execute();
$solicitacoes = $sql->fetchAll();


$nomeFuncionario = null;
if (count($solicitacoes) > 0) {
    $nomeFuncionario = $solicitacoes[0]['funcionario_nome'];
} else {

    $stmtNome = $conn->prepare("SELECT nome FROM funcionario WHERE id_funcionario = :id_funcionario");
    $stmtNome->bindValue(':id_funcionario', $idFuncionarioLogado, PDO::PARAM_INT);
    $stmtNome->execute();
    $nomeFuncionario = $stmtNome->fetchColumn() ?: 'Usuário';
}

function classeStatus($status)
{
    switch ($status) {
        case 'solicitado':
            return 'pendente';
        case 'andamento':
            return 'aprovado';
        case 'concluido':
            return 'aprovado';
        case 'cancelado':
        case 'recusado':
            return 'negado';
        default:
            return 'pendente';
    }
}

function classePrioridade($prioridade)
{
    switch (strtolower($prioridade)) {
        case 'baixa':
            return 'baixo';
        case 'media':
            return 'intermediaria';
        case 'alta':
        case 'urgente':
            return 'grave';
        default:
            return 'baixo';
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="css/perfil.css">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/perfil.js" defer></script>
</head>

<body>
    <div class="navbar">
        <img src="img/senai-logo-1.png" alt="">
        <ul>
            <?php if ($tipo == 'manutencao'): ?>
                <li><a href="chamado.php">Abrir Chamado</a></li>
                <li><a href="funcionario.php">Chamados</a></li>
            <?php elseif ($tipo == 'funcionario'): ?>
                <li><a href="chamado.php">Abrir Chamado</a></li>
                <li><a href="perfil.php">Perfil</a></li>
            <?php elseif ($tipo == 'admin'): ?>
                <li><a href="relatorio.php">Relatórios</a></li>
                <li><a href="cadastro.php">Cadastrar</a></li>
                <li><a href="ambiente.php">Ambientes/salas</a></li>
            <?php endif; ?>
            <li><a href="logout.php">Sair</a></li>
        </ul>
    </div>

    <section class="perfil">
        <img src="img/avatar.png" alt="Foto do usuário" class="avatar" />
        <div class="info">
            <div class="nome-area">
                <h1><?= htmlspecialchars($nomeFuncionario) ?></h1>
                <span class="tag">Manutenção</span>
            </div>

            <p class="email">✉️ pedro_coelho@gmail.com</p>
        </div>
    </section>

    <section class="card">
        <h2>Histórico de reparos concluídos</h2>
        <p class="descricao">Registro de todos os problemas resolvidos por você!</p>

        <?php if (count($solicitacoes) === 0): ?>
            <p>Você ainda não possui reparos registrados.</p>
        <?php else: ?>
            <?php foreach ($solicitacoes as $s):
                $classe = classeStatus($s['status']);
            ?>
                <div class="card-item" data-status="<?= $classe ?>">
                    <div class="card2">
                        <h3>Solicitação</h3>
                        <span class="<?= $classe ?>"><?= htmlspecialchars($s['status']) ?></span>
                    </div>

                    <p class="data"><?= date('d/m/Y H:i', strtotime($s['data_inic'])) ?></p>

                    <p class="text"><?= htmlspecialchars($s['motivo']) ?></p>

                    <p class="text">
                        Ambiente: <?= htmlspecialchars($s['ambiente_nome']) ?> - Sala <?= htmlspecialchars($s['num_sala']) ?> (<?= htmlspecialchars($s['sala_nome']) ?>)
                    </p>

                    <p class="urgencia <?= classePrioridade($s['prioridade']) ?>">
                        Urgência: <?= htmlspecialchars($s['prioridade']) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </section>

</body>

</html>