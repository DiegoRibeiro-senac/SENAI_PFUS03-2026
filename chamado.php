<?php
session_start();

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Se não estiver logado, manda de volta para o login
    header("Location: index.php?erro=Acesso negado. Faça login primeiro.");
    exit;
}

$id = $_SESSION['usuario_id'];
$tipo = $_SESSION['usuario_tipo'];
?>

<?php
require("db/conexao.php");

$msg = "";

// 1. Busca Ambientes e Todas as Salas (para o filtro visual)
$ambientes = $conn->query("SELECT id_ambiente, nome FROM ambiente")->fetchAll(PDO::FETCH_ASSOC);
$todas_salas = $conn->query("SELECT id_sala, nome, num_sala, id_ambiente FROM sala")->fetchAll(PDO::FETCH_ASSOC);

// 2. Processa o envio apenas quando clicar no botão "chamado"
if (isset($_POST['chamado'])) {

    // Pegamos os valores e limpamos espaços
    $descric    = trim($_POST['descric'] ?? '');
    $ambiente   = $_POST['ambiente'] ?? '';
    $sala       = $_POST['sala'] ?? '';
    $prioridade = $_POST['prioridade'] ?? '';

    // VALIDAÇÃO: Se algum campo estiver vazio ou for "0"
    if (empty($descric) || empty($ambiente) || empty($sala) || empty($prioridade)) {
        $msg = '<p style="color: red; font-weight: bold;">Por preencha os campos!</p>';
    } else {
        // Lógica da Foto (Upload)
        $nome_foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $novo_nome = md5(microtime()) . "." . $extensao;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $novo_nome)) {
                $nome_foto = $novo_nome;
            }
        }

        // INSERÇÃO
        $sql = $conn->prepare("INSERT INTO solicitacao (motivo, prioridade, id_ambiente, id_sala, foto, id_funcionario) VALUES (?, ?, ?, ?, ?, ?)");
        $status = $sql->execute([$descric, $prioridade, $ambiente, $sala, $nome_foto, $id]);

        if ($status) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?sucesso=1");
            exit;
        } else {
            $msg = "Erro ao salvar no banco de dados.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Chamado</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/jquery-3.7.1.min.js"></script>
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
        <div class="main">
            <div class="review-card">

                <h2>Abrir Chamado</h2>

                <form method="post" enctype="multipart/form-data">

                    <textarea name="descric" placeholder="Descreva seu problema..."><?= $_POST['descric'] ?? '' ?></textarea>



                    <select name="ambiente" id="select-ambiente" class="ambientes">
                        <option value="" selected disabled>Selecione o ambiente</option>
                        <?php foreach ($ambientes as $amb): ?>
                            <option value="<?= $amb['id_ambiente'] ?>"><?= $amb['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="sala" id="select-sala" class="salas" disabled>
                        <option value="" selected disabled>Selecione a sala</option>
                        <?php foreach ($todas_salas as $sala): ?>
                            <option value="<?= $sala['id_sala'] ?>" class="amb-<?= $sala['id_ambiente'] ?>" style="display:none;">
                                <?= $sala['nome'] ?> (<?= $sala['num_sala'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="form-group">
                        <select name="prioridade" class="prioridade">
                            <option value="" hidden disabled selected>Prioridade</option>
                            <option value="Baixa">Baixa</option>
                            <option value="Media">Média</option>
                            <option value="Alta">Alta</option>
                            <option value="Urgente">Urgente</option>
                        </select>
                        <div class="legenda-prioridades">
                            <small><strong>🟢 Baixa:</strong> Melhorias estéticas ou preventivas (Pintura, ajustes de móveis).</small><br>
                            <small><strong>🟡 Média:</strong> Defeitos que não impedem a aula (Lâmpada queimada, ruídos).</small><br>
                            <small><strong>🟠 Alta:</strong> Equipamento essencial parado ou ambiente comprometido (Ar-condicionado, máquinas).</small><br>
                            <small><strong>🔴 Urgente:</strong> Risco de vida ou parada total das aulas (Curto-circuito, vazamentos).</small>
                        </div>
                        <input type="file" name="foto" accept="image/*">

                        <button type="submit" name="chamado" class="chamado">
                            Enviar Chamado
                        </button>
                        <?= $msg ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#select-ambiente').change(function() {
                var ambID = $(this).val(); // Pega o ID do ambiente escolhido

                // Habilita o select de salas e reseta o valor
                $('#select-sala').prop('disabled', false).val("");

                // Esconde todas as opções de salas
                $('#select-sala option').hide();

                // Mostra a primeira opção (o placeholder)
                $('#select-sala option[value=""]').show();

                // Mostra apenas as salas que tem a classe do ambiente selecionado
                $('.amb-' + ambID).show();
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Verifica se a URL contém "sucesso=1"
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('sucesso')) {
                alert("Chamado aberto com sucesso!");

                // Opcional: Limpa a URL para o "sucesso=1" sumir da barra de endereços
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>

    <script>
        function atualizarDescricao() {
            const select = document.getElementById('prioridade');
            const ajuda = document.getElementById('texto-ajuda');

            const descricoes = {
                "Baixa": "🟡 **Baixa:** Melhorias estéticas ou preventivas (ex: pintura, ajustes de móveis). Sem prazo rígido.",
                "Media": "🔵 **Média:** Defeitos que não param a aula, mas precisam de correção (ex: lâmpada isolada queimada).",
                "Alta": "🟠 **Alta:** Equipamento de aula parado ou ambiente desconfortável (ex: ar-condicionado ou máquina principal).",
                "Urgente": "🔴 **Urgente:** Risco de acidentes, danos ao patrimônio ou interrupção total das aulas (ex: curto-circuito)."
            };

            ajuda.innerText = descricoes[select.value];
        }
    </script>

</body>

</html>