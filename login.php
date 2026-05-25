<?php
session_start();
require('db/conexao.php');

$msg_recuperacao = "";
$erro_recuperacao = "";

// Lógica de Redefinição de Senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['redefinir_senha'])) {
    $email = $_POST['email_reset'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';

    if (!empty($email) && !empty($nova_senha) && !empty($confirma_senha)) {
        if ($nova_senha === $confirma_senha) {
            // Verifica se o e-mail existe
            $stmt = $conn->prepare("SELECT id_funcionario FROM funcionario WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $upd = $conn->prepare("UPDATE funcionario SET senha_hash = ? WHERE email = ?");
                $upd->execute([$hash, $email]);
                $msg_recuperacao = "Senha alterada com sucesso! Faça login.";
            } else {
                $erro_recuperacao = "E-mail não encontrado no sistema.";
            }
        } else {
            $erro_recuperacao = "As senhas não coincidem.";
        }
    } else {
        $erro_recuperacao = "Preencha todos os campos.";
    }
}
?>

<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Feedbacks - SENAI</title>
    <link rel="stylesheet" href="css/estilo-senai.css">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/login.js" defer></script>
</head>

<body>
    <div class="container-login">

        <div class="lado-esquerdo">
            <div class="wrapper-formulario">
                <div class="logo-senai">SENAI</div>

                <h1 id="titulo-formulario">Boas-vindas ao Sistema</h1>
                <p class="subtitulo">Acesse sua conta para gerenciar os chamados de manutenção.</p>

                <!-- FORMULÁRIO DE LOGIN -->
                <form id="formLogin" action="login_processa.php" method="POST">
                    <label class="rotulo">E-mail</label>
                    <input type="email" class="campo-entrada" placeholder="seu@email.com" name="email" required>

                    <label class="rotulo">Senha</label>
                    <input type="password" class="campo-entrada" placeholder="Digite sua senha" name="senha" required>

                    <div style="text-align: right; margin-top: 8px;">
                        <a href="#" id="linkEsqueceuSenha" class="link-esqueceu">Esqueceu sua senha?</a>
                    </div>

                    <?php if (isset($_GET['erro'])): ?>
                        <p style="color:red; font-weight:bold; text-align:center; font-size:20px;"><?php echo $_GET['erro']; ?></p>
                    <?php endif; ?>

                    <?php if (!empty($msg_recuperacao)): ?>
                        <p style="color:green; font-weight:bold; text-align:center; margin-top:10px;"><?php echo $msg_recuperacao; ?></p>
                    <?php endif; ?>

                    <?php if (!empty($erro_recuperacao)): ?>
                        <p style="color:red; font-weight:bold; text-align:center; margin-top:10px;"><?php echo $erro_recuperacao; ?></p>
                    <?php endif; ?>

                    <button class="botao-primario" type="submit">Continuar</button>
                </form>

                <!-- FORMULÁRIO DE ESQUECEU SENHA -->
                <form id="formEsqueceuSenha" class="oculto" method="POST">
                    <h2 style="font-size: 20px; margin-bottom: 15px; color: #333;">Redefinir Senha</h2>

                    <label class="rotulo">Seu E-mail</label>
                    <input type="email" class="campo-entrada" name="email_reset" placeholder="Confirme seu e-mail" required>

                    <label class="rotulo">Nova Senha</label>
                    <input type="password" class="campo-entrada" id="inputNovaSenha" name="nova_senha" placeholder="Nova senha" required>

                    <label class="rotulo">Confirmar Senha</label>
                    <input type="password" class="campo-entrada" id="inputConfirmarNovaSenha" name="confirma_senha" placeholder="Repita a senha" required>
                    <div id="infoConfirmarNovaSenha" style="font-size: 12px; margin-top: 5px;"></div>

                    <button class="botao-primario" type="submit" name="redefinir_senha">Salvar Nova Senha</button>

                    <div style="text-align: center; margin-top: 15px;">
                        <a href="login.php" style="color: rgb(0, 92, 170); text-decoration: none; font-size: 14px;">Voltar ao Login</a>
                    </div>
                </form>

                <!-- Formulários ocultos para compatibilidade com o JS existente -->
                <form id="formCadastro" class="oculto"></form>
                <form id="formResetarSenha" class="oculto"></form>
            </div>
        </div>

        <div class="lado-direito">
            <div class="conteudo-promo">
                <div class="badge-promo">SENAI Sistema</div>
                <h2>Transforme Sua Gestão De Manutenção</h2>
                <p>A tecnologia elevando a eficiência da gestão e dos processos de manutenção em nossa plataforma integrada.</p>
            </div>
        </div>

    </div>
</body>

</html>