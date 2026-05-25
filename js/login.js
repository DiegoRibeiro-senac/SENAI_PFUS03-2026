// Aguardar carregamento completo do DOM
document.addEventListener('DOMContentLoaded', function () {

    const toggleForm = document.getElementById('toggleForm');
    const linkEsqueceuSenha = document.getElementById('linkEsqueceuSenha');
    const formLogin = document.getElementById('formLogin');
    const formCadastro = document.getElementById('formCadastro');
    const formEsqueceuSenha = document.getElementById('formEsqueceuSenha');
    const formResetarSenha = document.getElementById('formResetarSenha');
    const tituloFormulario = document.getElementById('titulo-formulario');

    // Alternar entre formulários de login e cadastro
    if (toggleForm) {
        toggleForm.addEventListener('click', function (e) {
            e.preventDefault();

            // Se está em esqueceu senha ou resetar senha, volta ao login
            if (!formEsqueceuSenha.classList.contains('oculto') || !formResetarSenha.classList.contains('oculto')) {
                formLogin.classList.remove('oculto');
                formCadastro.classList.add('oculto');
                formEsqueceuSenha.classList.add('oculto');
                formResetarSenha.classList.add('oculto');
                tituloFormulario.textContent = 'Boas-vindas ao Sistema';
                toggleForm.textContent = 'Criar uma nova conta';

                const subtitulo = document.querySelector('.subtitulo');
                if (subtitulo) subtitulo.remove();
                return;
            }

            if (formLogin.classList.contains('oculto')) {
                // Mostrar formulário de login
                formLogin.classList.remove('oculto');
                formCadastro.classList.add('oculto');
                formEsqueceuSenha.classList.add('oculto');
                formResetarSenha.classList.add('oculto');
                tituloFormulario.textContent = 'Boas-vindas ao Sistema';
                toggleForm.textContent = 'Criar uma nova conta';

                const subtitulo = document.querySelector('.subtitulo');
                if (subtitulo) subtitulo.remove();
            } else {
                // Mostrar formulário de cadastro
                formLogin.classList.add('oculto');
                formCadastro.classList.remove('oculto');
                formEsqueceuSenha.classList.add('oculto');
                formResetarSenha.classList.add('oculto');
                tituloFormulario.textContent = 'Criar Conta';
                toggleForm.textContent = 'Já tenho conta';

                if (!document.querySelector('.subtitulo')) {
                    const subtitulo = document.createElement('p');
                    subtitulo.className = 'subtitulo';
                    subtitulo.textContent = 'Preencha os dados abaixo para criar sua conta no sistema.';
                    tituloFormulario.after(subtitulo);
                }
            }
        });
    }

    // Link "Esqueceu sua senha?"
    if (linkEsqueceuSenha) {
        linkEsqueceuSenha.addEventListener('click', function (e) {
            e.preventDefault();

            formLogin.classList.add('oculto');
            formCadastro.classList.add('oculto');
            formEsqueceuSenha.classList.remove('oculto');
            formResetarSenha.classList.add('oculto');
            tituloFormulario.textContent = 'Recuperar Senha';
            toggleForm.textContent = 'Voltar ao login';

            // Adicionar subtítulo
            const subtituloExistente = document.querySelector('.subtitulo');
            if (subtituloExistente) subtituloExistente.remove();

            const subtitulo = document.createElement('p');
            subtitulo.className = 'subtitulo';
            subtitulo.textContent = 'Informe seu email para receber o link de recuperação.';
            tituloFormulario.after(subtitulo);
        });
    }

    // Validação em tempo real da senha (CADASTRO)
    const inputSenha = document.getElementById('inputSenha');
    const inputConfirmarSenha = document.getElementById('inputConfirmarSenha');
    const infoConfirmarSenha = document.getElementById('infoConfirmarSenha');

    if (inputSenha) {
        inputSenha.addEventListener('input', function () {
            validarSenha(this.value, 'req-min', 'req-mai', 'req-min-letra', 'req-num', 'req-esp');
            verificarConfirmacaoSenha();
        });
    }

    if (inputConfirmarSenha) {
        inputConfirmarSenha.addEventListener('input', verificarConfirmacaoSenha);
    }

    function verificarConfirmacaoSenha() {
        if (!inputSenha || !inputConfirmarSenha || !infoConfirmarSenha) return;

        const senha = inputSenha.value;
        const confirmarSenha = inputConfirmarSenha.value;

        if (confirmarSenha.length > 0) {
            if (senha === confirmarSenha) {
                infoConfirmarSenha.textContent = '✓ As senhas coincidem';
                infoConfirmarSenha.classList.add('sucesso');
                infoConfirmarSenha.classList.remove('erro');
            } else {
                infoConfirmarSenha.textContent = '✗ As senhas não coincidem';
                infoConfirmarSenha.classList.add('erro');
                infoConfirmarSenha.classList.remove('sucesso');
            }
        } else {
            infoConfirmarSenha.textContent = '';
            infoConfirmarSenha.classList.remove('sucesso', 'erro');
        }
    }

    // Validação em tempo real da senha (RESETAR SENHA)
    const inputNovaSenha = document.getElementById('inputNovaSenha');
    const inputConfirmarNovaSenha = document.getElementById('inputConfirmarNovaSenha');
    const infoConfirmarNovaSenha = document.getElementById('infoConfirmarNovaSenha');

    if (inputNovaSenha) {
        inputNovaSenha.addEventListener('input', function () {
            validarSenha(this.value, 'req-min-reset', 'req-mai-reset', 'req-min-letra-reset', 'req-num-reset', 'req-esp-reset');
            verificarConfirmacaoNovaSenha();
        });
    }

    if (inputConfirmarNovaSenha) {
        inputConfirmarNovaSenha.addEventListener('input', verificarConfirmacaoNovaSenha);
    }

    function verificarConfirmacaoNovaSenha() {
        if (!inputNovaSenha || !inputConfirmarNovaSenha || !infoConfirmarNovaSenha) return;

        const senha = inputNovaSenha.value;
        const confirmarSenha = inputConfirmarNovaSenha.value;

        if (confirmarSenha.length > 0) {
            if (senha === confirmarSenha) {
                infoConfirmarNovaSenha.textContent = '✓ As senhas coincidem';
                infoConfirmarNovaSenha.classList.add('sucesso');
                infoConfirmarNovaSenha.classList.remove('erro');
            } else {
                infoConfirmarNovaSenha.textContent = '✗ As senhas não coincidem';
                infoConfirmarNovaSenha.classList.add('erro');
                infoConfirmarNovaSenha.classList.remove('sucesso');
            }
        } else {
            infoConfirmarNovaSenha.textContent = '';
            infoConfirmarNovaSenha.classList.remove('sucesso', 'erro');
        }
    }

    // Função auxiliar para validar senha
    function validarSenha(senha, idMin, idMai, idMinLetra, idNum, idEsp) {
        // Verificar comprimento mínimo
        const reqMin = document.getElementById(idMin);
        if (reqMin) {
            if (senha.length >= 8) {
                reqMin.classList.add('valido');
                reqMin.classList.remove('invalido');
                reqMin.textContent = '✓ Mínimo de 8 caracteres';
            } else {
                reqMin.classList.remove('valido');
                reqMin.classList.add('invalido');
                reqMin.textContent = '✗ Mínimo de 8 caracteres';
            }
        }

        // Verificar letra maiúscula
        const reqMai = document.getElementById(idMai);
        if (reqMai) {
            if (/[A-Z]/.test(senha)) {
                reqMai.classList.add('valido');
                reqMai.classList.remove('invalido');
                reqMai.textContent = '✓ Pelo menos uma letra maiúscula (A-Z)';
            } else {
                reqMai.classList.remove('valido');
                reqMai.classList.add('invalido');
                reqMai.textContent = '✗ Pelo menos uma letra maiúscula (A-Z)';
            }
        }

        // Verificar letra minúscula
        const reqMinLetra = document.getElementById(idMinLetra);
        if (reqMinLetra) {
            if (/[a-z]/.test(senha)) {
                reqMinLetra.classList.add('valido');
                reqMinLetra.classList.remove('invalido');
                reqMinLetra.textContent = '✓ Pelo menos uma letra minúscula (a-z)';
            } else {
                reqMinLetra.classList.remove('valido');
                reqMinLetra.classList.add('invalido');
                reqMinLetra.textContent = '✗ Pelo menos uma letra minúscula (a-z)';
            }
        }

        // Verificar número
        const reqNum = document.getElementById(idNum);
        if (reqNum) {
            if (/[0-9]/.test(senha)) {
                reqNum.classList.add('valido');
                reqNum.classList.remove('invalido');
                reqNum.textContent = '✓ Pelo menos um número (0-9)';
            } else {
                reqNum.classList.remove('valido');
                reqNum.classList.add('invalido');
                reqNum.textContent = '✗ Pelo menos um número (0-9)';
            }
        }

        // Verificar caractere especial
        const reqEsp = document.getElementById(idEsp);
        if (reqEsp) {
            if (/[!@#$%^&*(),.?":{}|<>]/.test(senha)) {
                reqEsp.classList.add('valido');
                reqEsp.classList.remove('invalido');
                reqEsp.textContent = '✓ Pelo menos um caractere especial (!@#$%...)';
            } else {
                reqEsp.classList.remove('valido');
                reqEsp.classList.add('invalido');
                reqEsp.textContent = '✗ Pelo menos um caractere especial (!@#$%...)';
            }
        }
    }
});