<?php
session_start();
require('db/conexao.php');

// 1. Verificação de Acesso
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: index.php?erro=Acesso negado.");
    exit;
}

$tipo = $_SESSION['usuario_tipo'];

// 2. Processamento POST Único
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cadastro_ambiente'])) {
        $sql = $conn->prepare("INSERT INTO ambiente (nome) VALUES (?)");
        $sql->execute([LimpaPost($_POST['nome_ambiente'])]);
        header("Location: ambiente.php?sucesso=1");
    } elseif (isset($_POST['cadastro_sala'])) {
        $sql = $conn->prepare("INSERT INTO sala (id_ambiente, nome, num_sala) VALUES (?, ?, ?)");
        $sql->execute([LimpaPost($_POST['id_ambiente']), LimpaPost($_POST['nome_sala']), LimpaPost($_POST['numero_sala'])]);
        header("Location: ambiente.php?sucesso=1&aba=sala");
    } elseif (isset($_POST['atualizar_ambiente'])) {
        $sql = $conn->prepare("UPDATE ambiente SET nome = ? WHERE id_ambiente = ?");
        $sql->execute([LimpaPost($_POST['nome_ambiente_edit']), LimpaPost($_POST['id_ambiente_edit'])]);
        header("Location: ambiente.php?sucesso=1&aba=lista");
    } elseif (isset($_POST['atualizar_sala'])) {
        $sql = $conn->prepare("UPDATE sala SET nome = ?, num_sala = ? WHERE id_sala = ?");
        $sql->execute([LimpaPost($_POST['nome_sala_edit']), LimpaPost($_POST['num_sala_edit']), LimpaPost($_POST['id_sala_edit'])]);
        header("Location: ambiente.php?sucesso=1&aba=lista");
    }
    exit;
}

// 3. Consultas
$ambientes = $conn->query("SELECT id_ambiente, nome FROM ambiente ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$lista_geral = $conn->query("SELECT s.id_sala, s.nome as nome_sala, s.num_sala, a.id_ambiente, a.nome as nome_ambiente 
                             FROM sala s JOIN ambiente a ON s.id_ambiente = a.id_ambiente 
                             ORDER BY a.nome, s.nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambientes e Salas - SENAI</title>
    <link rel="stylesheet" href="css/ambiente.css">
    <script src="js/jquery-3.7.1.min.js"></script>
    <style>
        .secao-oculta {
            display: none;
        }

        .text-center {
            text-align: center;
        }
    </style>
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

    <div class="alternar">
        <button data-target="#form-ambiente" class="btn-nav botao-primario">Novo Ambiente</button>
        <button data-target="#form-sala" class="btn-nav botao-primario">Nova Sala</button>
        <button data-target="#secao-lista" class="btn-nav botao-primario">Gerenciar / Editar</button>
    </div>

    <div class="container-conteudo">
        <form id="form-ambiente" class="formulario-cadastro" method="POST">
            <h2>Novo Ambiente</h2>
            <input type="text" name="nome_ambiente" class="campo-entrada" placeholder="Nome do Ambiente" required>
            <button class="botao-primario" type="submit" name="cadastro_ambiente">Salvar Ambiente</button>
        </form>

        <form id="form-sala" class="formulario-cadastro secao-oculta" method="POST">
            <h2>Nova Sala</h2>
            <select name="id_ambiente" class="campo-entrada" required>
                <option value="">Selecione o ambiente</option>
                <?php foreach ($ambientes as $amb): ?>
                    <option value="<?= $amb['id_ambiente'] ?>"><?= $amb['nome'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="nome_sala" class="campo-entrada" placeholder="Nome da Sala" required>
            <input type="text" name="numero_sala" class="campo-entrada" placeholder="Número">
            <button class="botao-primario" type="submit" name="cadastro_sala">Salvar Sala</button>
        </form>

        <div id="secao-lista" class="formulario-cadastro secao-oculta">
            <h2>Gerenciar Dados</h2>
            <table width="100%">
                <thead>
                    <tr>
                        <th>Ambiente</th>
                        <th>Sala</th>
                        <th>Nº</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista_geral as $item): ?>
                        <tr>
                            <td><?= $item['nome_ambiente'] ?> <button class="btn-edit-amb" data-id="<?= $item['id_ambiente'] ?>" data-nome="<?= $item['nome_ambiente'] ?>">✏️</button></td>
                            <td><?= $item['nome_sala'] ?></td>
                            <td><?= $item['num_sala'] ?></td>
                            <td class="text-center">
                                <button class="btn-edit-sala" data-id="<?= $item['id_sala'] ?>" data-nome="<?= $item['nome_sala'] ?>" data-num="<?= $item['num_sala'] ?>">Editar Sala</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <form id="form-edit-ambiente" class="formulario-cadastro secao-oculta" method="POST">
            <h2>Editar Ambiente</h2>
            <input type="hidden" name="id_ambiente_edit" id="id_amb_edit">
            <input type="text" name="nome_ambiente_edit" id="nome_amb_edit" class="campo-entrada" required>
            <button type="submit" name="atualizar_ambiente" class="botao-primario">Atualizar</button>
            <button type="button" class="btn-cancelar">Cancelar</button>
        </form>

        <form id="form-edit-sala" class="formulario-cadastro secao-oculta" method="POST">
            <h2>Editar Sala</h2>
            <input type="hidden" name="id_sala_edit" id="id_sala_edit">
            <input type="text" name="nome_sala_edit" id="nome_sala_edit" class="campo-entrada" required>
            <input type="text" name="num_sala_edit" id="num_sala_edit" class="campo-entrada">
            <button type="submit" name="atualizar_sala" class="botao-primario">Atualizar</button>
            <button type="button" class="btn-cancelar">Cancelar</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            const esconderTudo = () => $('.formulario-cadastro').hide();

            $('.btn-nav').click(function() {
                esconderTudo();
                $($(this).data('target')).fadeIn();
            });

            $('.btn-edit-amb').click(function() {
                esconderTudo();
                $('#id_amb_edit').val($(this).data('id'));
                $('#nome_amb_edit').val($(this).data('nome'));
                $('#form-edit-ambiente').fadeIn();
            });

            $('.btn-edit-sala').click(function() {
                esconderTudo();
                $('#id_sala_edit').val($(this).data('id'));
                $('#nome_sala_edit').val($(this).data('nome'));
                $('#num_sala_edit').val($(this).data('num'));
                $('#form-edit-sala').fadeIn();
            });

            $('.btn-cancelar').click(function() {
                esconderTudo();
                $('#secao-lista').fadeIn();
            });

            const params = new URLSearchParams(window.location.search);
            if (params.get('aba') === 'sala') {
                esconderTudo();
                $('#form-sala').show();
            }
            if (params.get('aba') === 'lista') {
                esconderTudo();
                $('#secao-lista').show();
            }
            if (params.has('sucesso')) alert("Sucesso!");
        });
    </script>
</body>

</html>