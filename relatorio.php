<?php
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Se não estiver logado, manda de volta para o login
    header("Location: index.php");
    exit;
}

$id = $_SESSION['usuario_id'];
$tipo = $_SESSION['usuario_tipo'];
?>

<?php
require("db/conexao.php");

$sqlGeral = $conn->prepare("
    SELECT 
        COUNT(*) AS total,
        SUM(status = 'concluido') AS concluidos,
        SUM(status = 'andamento') AS andamento,
        SUM(status = 'cancelado') AS cancelado,
        SUM(status = 'recusado') AS recusado,
        SUM(status = 'solicitado') AS pendentes
    FROM solicitacao
");

$sqlGeral->execute();
$resumoGeral = $sqlGeral->fetch(PDO::FETCH_ASSOC);

$sql = $conn->prepare("
    SELECT 
        s.id_solicitacao,
        s.motivo,
        s.status,
        s.prioridade,
        s.data_inic,
        s.data_fim,
        a.nome AS ambiente,
        sa.num_sala,
        f.nome AS funcionario
    FROM solicitacao s
    INNER JOIN ambiente a ON s.id_ambiente = a.id_ambiente
    INNER JOIN sala sa ON s.id_sala = sa.id_sala
    LEFT JOIN funcionario f ON s.id_funcionario = f.id_funcionario
");

$sql->execute();
$lista = $sql->fetchAll(PDO::FETCH_ASSOC);


$sqlFuncionario = $conn->prepare("
    SELECT 
        f.nome AS funcionario,
        COUNT(s.id_solicitacao) AS total,
        SUM(s.status = 'concluido') AS concluidos,
        SUM(s.status = 'andamento') AS andamento,
        SUM(s.status = 'cancelado') AS cancelado,
        SUM(s.status = 'recusado') AS recusado,
        SUM(s.status = 'solicitado') AS pendentes
    FROM solicitacao s
    LEFT JOIN funcionario f 
        ON s.id_funcionario = f.id_funcionario
    GROUP BY f.nome
");

$sqlFuncionario->execute();
$resumos = $sqlFuncionario->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="js/jquery-3.7.1.min.js"></script>
    <title>Relatório</title>
    <link rel="stylesheet" href="css/relatorio.css">
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

    <div class="container">

        <!-- CARDS RESUMO -->
        <div class="resumoTotal">
            <div class="card azul">
                <h3>Total de Chamados</h3>
                <p><?= $resumoGeral['total'] ?></p>
            </div>

            <div class="card vermelho">
                <h3>Pendentes</h3>
                <p><?= $resumoGeral['pendentes'] ?></p>
            </div>

            <div class="card verde">
                <h3>Concluídos</h3>
                <p><?= $resumoGeral['concluidos'] ?></p>
            </div>

        </div>
        <p id="contador"></p>


        <!-- TABELA -->

        <div class="card">

            <h2>Relatório de Manutenções</h2>
            <div class="filtros">

                <select id="filtroStatus">
                    <option value="">Todos</option>
                    <option value="solicitado">Solicitado</option>
                    <option value="andamento">Andamento</option>
                    <option value="concluido">Concluído</option>
                </select>

                <select id="filtroPrioridade">
                    <option value="">Todas</option>
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>

                <input class="pesquisa" type="text" id="campo" placeholder="Pesquisar funcionário...">


            </div>
            <table>
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                        <th>Ambiente</th>
                        <th>Início</th>
                        <th>Fim</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($lista as $l):
                        $statusClasse = $l['status'] == 'concluido' ? 'concluida' : ($l['status'] == 'solicitado' ? 'pendente' : $l['status']);
                        $prioridadeClasse = strtolower($l['prioridade']);
                    ?>
                        <tr>
                            <td data-label="Funcionário" class="nome-funcionario"><?= $l['funcionario'] ?? 'Não atribuído' ?></td>
                            <td data-label="Descrição"><?= $l['motivo'] ?></td>
                            <td data-label="Status"><span class="status <?= $statusClasse ?>"><?= $l['status'] ?></span></td>
                            <td data-label="Prioridade"><span class="prioridade <?= $prioridadeClasse ?>"><?= $l['prioridade'] ?></span></td>
                            <td data-label="Ambiente"><?= $l['ambiente'] ?> - Sala <?= $l['num_sala'] ?></td>
                            <td data-label="Início"><?= $l['data_inic'] ?? 'N/A' ?></td>
                            <td data-label="Fim"><?= $l['data_fim'] ?? 'N/A' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>

    <!-- FILTRAR NOME -->
    <script>
        const campo = document.getElementById('campo');
        const contador = document.getElementById('contador');

        function atualizarTabela() {
            const linhas = document.querySelectorAll('tbody tr');
            let total = 0;

            linhas.forEach(linha => {

                const nome = linha.querySelector('.nome-funcionario').innerText.toLowerCase();
                const mostrar = nome.includes(campo.value.toLowerCase());

                linha.style.display = mostrar ? '' : 'none';
                if (mostrar) total++;
            });

            contador.textContent = `Concluidos: ${total}`;
        }

        campo.addEventListener('input', atualizarTabela);
    </script>


    <!-- FILTRAR PRIORIDADE -->
    <script>
        document.getElementById('filtroPrioridade').addEventListener('change', function() {
            const valor = this.value.toLowerCase().trim();
            const linhas = document.querySelectorAll('tbody tr');

            linhas.forEach(linha => {
                const prioridade = linha.querySelector('.prioridade').textContent.toLowerCase().trim();

                if (valor === "" || prioridade === valor) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        });
    </script>


    <!-- FILTRAR STATUS -->
    <script>
        document.getElementById('filtroStatus').addEventListener('change', function() {
            const valor = this.value.toLowerCase().trim();
            const linhas = document.querySelectorAll('tbody tr');

            linhas.forEach(linha => {
                const status = linha.querySelector('.status').innerText.toLowerCase().trim();
                linha.style.display = (valor === "" || status.includes(valor)) ? '' : 'none';
            });
        });
    </script>



    <!-- PAGINA DE FUNCIONARIO -->

    <div class="statusFuncionario">
        <h2>Pesquisar Funcionário</h2>

        <input type="text" id="campoFuncionario"
            placeholder="Pesquisar funcionário...">

        <div class="cardsFuncionario">
            <?php foreach ($resumos as $resumo): ?>

                <div class="cardFuncionario">

                    <h4 class="nome-funcionario">
                        <?= $resumo['funcionario'] ?? 'Não atribuído' ?>
                    </h4>

                    <p>Total: <?= $resumo['total'] ?></p>
                    <p>Concluídos: <?= $resumo['concluidos'] ?></p>
                    <p>Andamento: <?= $resumo['andamento'] ?></p>
                    <p>Pendentes: <?= $resumo['pendentes'] ?></p>
                    <p>Cancelado: <?= $resumo['cancelado'] ?></p>
                    <p>Recusado: <?= $resumo['recusado'] ?></p>

                </div>

            <?php endforeach; ?>
        </div>
    </div>

    <script>
        const campoFuncionario = document.getElementById('campoFuncionario');
        const cards = document.querySelectorAll('.cardFuncionario');

        function atualizar() {
            const valor = campoFuncionario.value.toLowerCase();
            let apareceu = false;

            cards.forEach(card => {
                const nome = card.querySelector('.nome-funcionario').innerText.toLowerCase();

                if (!apareceu && valor && nome.includes(valor)) {
                    card.style.display = '';
                    apareceu = true; // mostra só o primeiro
                } else {
                    card.style.display = 'none';
                }
            });
        }

        atualizar(); // começa escondido
        campoFuncionario.addEventListener('input', atualizar);
    </script>
    </div>

</body>

</html>