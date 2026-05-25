<?php
session_start();

// 1. Verificação de Acesso
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php?erro=Acesso negado. Faça login primeiro.");
    exit;
}

require("db/conexao.php");

$id_func_logado = $_SESSION['usuario_id'];
$tabAtiva = $_GET['tab'] ?? 'servicos';
$dataFiltro = $_GET['data_filtro'] ?? null;

// 2. Processamento de Ações (POST)
if (isset($_POST['acao'], $_POST['id'])) {
    $id = (int)$_POST['id'];
    $acao = $_POST['acao'];

    switch ($acao) {
        case 'aceitar':
            $sql = $conn->prepare("UPDATE solicitacao SET status = 'andamento', data_inic = NOW(), id_funcionario = ? WHERE id_solicitacao = ?");
            $sql->execute([$id_func_logado, $id]);
            break;
        case 'concluir':
            $sql = $conn->prepare("UPDATE solicitacao SET status = 'concluido', data_fim = NOW(), id_funcionario_concluiu = ? WHERE id_solicitacao = ?");
            $sql->execute([$id_func_logado, $id]);
            break;
        case 'cancelar':
            $motivo = htmlspecialchars($_POST['motivo_cancelamento'] ?? '');
            if ($id > 0 && $motivo !== '') {
                $sql = $conn->prepare("UPDATE solicitacao SET status = 'solicitado', id_funcionario = NULL, id_funcionario_cancelou = ?, motivo_cancelamento = ?, data_cancelamento = NOW(), data_inic = NULL WHERE id_solicitacao = ?");
                $sql->execute([$id_func_logado, $motivo, $id]);
            }
            break;
    }
    header("Location: funcionario.php?tab=" . ($_POST['redirect'] ?? 'servicos'));
    exit;
}

// 3. Função Unificada de Busca
function buscarSolicitacoes($conn, $status, $cancelados = false, $dataFiltro = null, $id_func)
{
    $order = "ORDER BY FIELD(s.prioridade, 'Urgente', 'Alta', 'Média', 'Baixa'), s.id_solicitacao DESC";
    $params = [];

    $query = "SELECT s.*, sala.nome AS sala_nome, sala.num_sala, amb.nome AS ambiente_nome, 
                     f_concluiu.nome AS nome_concluiu, f_cancelou.nome AS nome_cancelou
              FROM solicitacao s
              JOIN sala ON s.id_sala = sala.id_sala
              JOIN ambiente amb ON s.id_ambiente = amb.id_ambiente
              LEFT JOIN funcionario f_concluiu ON s.id_funcionario_concluiu = f_concluiu.id_funcionario
              LEFT JOIN funcionario f_cancelou ON s.id_funcionario_cancelou = f_cancelou.id_funcionario WHERE ";

    if ($cancelados) {
        $query .= "(s.id_funcionario_cancelou = ? OR s.id_funcionario = ?) AND s.motivo_cancelamento IS NOT NULL";
        $params = [$id_func, $id_func];
        if ($dataFiltro) {
            $query .= " AND DATE(s.data_cancelamento) = ?";
            $params[] = $dataFiltro;
        }
    } else {
        $placeholders = implode(',', array_fill(0, count($status), '?'));
        $query .= "s.status IN ($placeholders)";
        $params = $status;

        if (in_array('andamento', $status) || in_array('concluido', $status)) {
            $query .= " AND s.id_funcionario = ?";
            $params[] = $id_func;
        }
        if (in_array('concluido', $status) && $dataFiltro) {
            $query .= " AND DATE(s.data_fim) = ?";
            $params[] = $dataFiltro;
        }
    }

    $query .= " GROUP BY s.id_solicitacao $order";
    $sql = $conn->prepare($query);
    $sql->execute($params);
    return $sql->fetchAll();
}

// 4. Carregamento de Dados
$solicitados = ($tabAtiva === 'servicos')   ? buscarSolicitacoes($conn, ['solicitado'], false, null, $id_func_logado) : [];
$andamento   = ($tabAtiva === 'terminar')   ? buscarSolicitacoes($conn, ['andamento'], false, null, $id_func_logado) : [];
$finalizados = ($tabAtiva === 'concluido')  ? buscarSolicitacoes($conn, ['concluido'], false, $dataFiltro, $id_func_logado) : [];
$cancelados  = ($tabAtiva === 'cancelados') ? buscarSolicitacoes($conn, [], true, $dataFiltro, $id_func_logado) : [];

$listaAtual = match ($tabAtiva) {
    'servicos'   => $solicitados,
    'terminar'   => $andamento,
    'concluido'  => $finalizados,
    'cancelados' => $cancelados,
    default      => []
};
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo.css">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/funcionario.js" defer></script>
    <title>Painel do Funcionário</title>
</head>

<body>
    <nav class="navbar">
        <img src="img/senai-logo-1.png" alt="Logo">
        <div class="menu">
            <a href="?tab=servicos" class="nav-link <?= $tabAtiva === 'servicos' ? 'active' : '' ?>">Serviços</a>
            <a href="?tab=terminar" class="nav-link <?= $tabAtiva === 'terminar' ? 'active' : '' ?>">Terminar Serviços</a>
            <a href="?tab=concluido" class="nav-link <?= $tabAtiva === 'concluido' ? 'active' : '' ?>">Serviços Concluído</a>
            <a href="?tab=cancelados" class="nav-link <?= $tabAtiva === 'cancelados' ? 'active' : '' ?>">Serviços Cancelados</a>
            <a href="chamado.php" class="nav-link">Voltar a Chamados</a>
        </div>
    </nav>

    <div class="filtro-container">
        <?php if ($tabAtiva === 'servicos'): ?>
            <label for="filtro-prioridade">Filtrar por Prioridade:</label>
            <select id="filtro-prioridade" class="select-prioridade">
                <option value="todos">Exibir Todos</option>
                <option value="baixa">🟢 Baixa</option>
                <option value="media">🟡 Média</option>
                <option value="alta">🟠 Alta</option>
                <option value="urgente">🔴 Urgente</option>
            </select>
        <?php endif; ?>

        <?php if ($tabAtiva === 'concluido' || $tabAtiva === 'cancelados'): ?>
            <form method="GET" style="display: flex; align-items: center; gap: 10px; width: 100%;">
                <input type="hidden" name="tab" value="<?= $tabAtiva ?>">
                <label>Filtrar por Dia:</label>
                <input type="date" name="data_filtro" value="<?= $dataFiltro ?>" class="select-prioridade" style="max-width: 200px;">
                <button type="submit" class="btn btn-detalhes" style="width: auto;">Filtrar</button>
                <?php if ($dataFiltro): ?>
                    <a href="?tab=<?= $tabAtiva ?>" class="btn btn-cancelar" style="text-decoration: none; font-size: 12px;">Limpar</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

    <main class="container tab-ativo">
        <?php if (empty($listaAtual)): ?>
            <p>Nenhum registro encontrado.</p>
            <?php else:
            foreach ($listaAtual as $s): ?>
                <div class="coluna">
                    <div class="tag-prioridade <?= strtolower($s['prioridade']) ?>"><?= $s['prioridade'] ?></div>
                    <div class="card">
                        <strong><?= $s['motivo'] ?></strong>
                        <p><?= $s['ambiente_nome'] ?> - <?= $s['sala_nome'] ?></p>
                        <div class="botoes">
                            <button type="button" class="btn btn-detalhes btn-abrir-detalhes" data-info='<?= json_encode($s) ?>' style="width: 100%;">Ver Detalhes</button>
                        </div>
                    </div>
                </div>
        <?php endforeach;
        endif; ?>
    </main>

    <div id="modal-detalhes" class="modal">
        <div class="modal-conteudo">
            <button type="button" class="modal-fechar">&times;</button>
            <h2>Detalhes do Chamado</h2>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <table class="tabela-detalhes">
                    <tr id="row-foto" style="display:none;">
                        <td colspan="2" style="text-align:center;"><img id="det-foto" src="" style="max-width:100%; border-radius:8px;"></td>
                    </tr>
                    <tr>
                        <td><strong>Motivo:</strong></td>
                        <td id="det-motivo"></td>
                    </tr>
                    <tr>
                        <td><strong>Ambiente:</strong></td>
                        <td id="det-ambiente"></td>
                    </tr>
                    <tr>
                        <td><strong>Sala:</strong></td>
                        <td id="det-sala"></td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td id="det-status"></td>
                    </tr>
                </table>

                <?php if (!in_array($tabAtiva, ['cancelados', 'concluido'])): ?>
                    <div id="acoes-modal" style="margin-top: 20px;">
                        <div id="wrapper-aceitar" style="display:none;">
                            <form method="post">
                                <input type="hidden" name="acao" value="aceitar">
                                <input type="hidden" name="id" class="det-id-input">
                                <input type="hidden" name="redirect" value="servicos">
                                <button type="submit" class="btn btn-aceitar" style="width:100%;">Aceitar Serviço</button>
                            </form>
                        </div>
                        <div id="wrapper-terminar" style="display:none; flex-direction: column; gap: 10px;">
                            <form method="post">
                                <input type="hidden" name="acao" value="concluir">
                                <input type="hidden" name="id" class="det-id-input">
                                <input type="hidden" name="redirect" value="terminar">
                                <button type="submit" class="btn btn-concluir" style="width:100%;">Concluir Serviço</button>
                            </form>
                            <button type="button" class="btn btn-cancelar btn-cancelar-no-modal" style="width:100%;">Cancelar Serviço</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="modal-cancelar" class="modal">
        <div class="modal-conteudo">
            <button type="button" class="modal-fechar">&times;</button>
            <h2>Cancelar Serviço</h2>
            <form method="post">
                <input type="hidden" name="acao" value="cancelar">
                <input type="hidden" name="id" id="cancel-id">
                <input type="hidden" name="redirect" value="cancelados">
                <label>Motivo do Cancelamento:</label>
                <textarea name="motivo_cancelamento" id="motivo-cancelamento" required></textarea>
                <button type="submit" class="btn btn-cancelar" style="width:100%;">Confirmar Cancelamento</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = "Enviando...";
                }
            });
        });
    </script>
</body>

</html>