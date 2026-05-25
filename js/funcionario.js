// --- UTILITÁRIOS ---
const formatarData = (d) => d ? new Date(d).toLocaleString('pt-BR') : "--/--/--";

const calcularTempo = (inicio, fim) => {
  if (!inicio || !fim) return "N/A";
  const diff = Math.abs(new Date(fim) - new Date(inicio));
  const dias = Math.floor(diff / 86400000);
  const horas = Math.floor((diff % 86400000) / 3600000);
  const minutos = Math.floor((diff % 3600000) / 60000);
  const segundos = Math.floor((diff % 60000) / 1000);
  return `${dias} dias, ${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;
};

// --- INICIALIZAÇÃO PRINCIPAL ---
$(document).ready(function () {

  // 1. Navegação (Links da Navbar)
  $(".nav-link").on("click", function () {
    const targetTab = $(this).data('tab');
    if (targetTab) window.location.href = `funcionario.php?tab=${targetTab}`;
  });

  // 2. Filtro de Prioridade
  $('#filtro-prioridade').on('change', function () {
    const pSelecionada = $(this).val();
    $('.coluna').each(function () {
      const tag = $(this).find('.tag-prioridade');
      const exibir = pSelecionada === 'todos' || tag.hasClass(pSelecionada);
      $(this).toggle(exibir).css('display', exibir ? 'flex' : 'none');
    });
  });

  // 3. Gestão de Modais
  // Fecha modais (Botão X ou clicar fora)
  $(document).on('click', function (e) {
    if ($(e.target).hasClass('modal-fechar')) $(e.target).closest('.modal').hide();
    if ($(e.target).hasClass('modal')) $(e.target).hide();
  });

  // Abre Modal de Detalhes
  $(document).on('click', '.btn-abrir-detalhes', function () {
    const s = $(this).data('info');
    const $tabela = $('.tabela-detalhes');

    // Preenchimento básico
    $('#det-motivo').text(s.motivo);
    $('#det-ambiente').text(s.ambiente_nome);
    $('#det-sala').text(`${s.sala_nome} (${s.num_sala})`);
    $('#det-status').text(s.status);
    $('.det-id-input').val(s.id_solicitacao);

    // Limpa e reconstrói linhas dinâmicas
    $('.linha-dinamica').remove();

    if (s.status === 'concluido') {
      $tabela.append(`
        <tr class="linha-dinamica"><td><strong>Início:</strong></td><td>${formatarData(s.data_inic)}</td></tr>
        <tr class="linha-dinamica"><td><strong>Fim:</strong></td><td>${formatarData(s.data_fim)}</td></tr>
        <tr class="linha-dinamica"><td><strong>Tempo Total:</strong></td><td>${calcularTempo(s.data_inic, s.data_fim)}</td></tr>
        <tr class="linha-dinamica"><td><strong>Concluído por:</strong></td><td>${s.nome_concluiu || '---'}</td></tr>
      `);
    }

    if (s.motivo_cancelamento) {
      $tabela.append(`
        <tr class="linha-dinamica"><td><strong>Cancelado por:</strong></td><td>${s.nome_cancelou || '---'}</td></tr>
        <tr class="linha-dinamica"><td><strong>Motivo:</strong></td><td>${s.motivo_cancelamento}</td></tr>
      `);
    }

    // Botões de ação
    $('#wrapper-aceitar').toggle(s.status === 'solicitado');
    $('#wrapper-terminar').css('display', s.status === 'andamento' ? 'flex' : 'none');

    // Foto do serviço
    if (s.foto) {
      $('#det-foto').attr('src', 'uploads/' + s.foto);
      $('#row-foto').show();
    } else {
      $('#row-foto').hide();
    }

    $('#modal-detalhes').css('display', 'flex');
  });

  // Alternar para Modal de Cancelamento
  $(document).on('click', '.btn-cancelar-no-modal', function () {
    const idAtual = $('.det-id-input').val();
    $('#modal-detalhes').hide();
    $('#cancel-id').val(idAtual);
    $('#motivo-cancelamento').val('');
    $('#modal-cancelar').css('display', 'flex');
  });
});