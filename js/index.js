const conteudo = document.getElementById("conteudo");

const paginas = {
    Serviços: `
    <div class="coluna">
      <h3>Consertar o Ar-Condicionado</h3>
      <div class="card">
        <div class="tag verde"></div>
        <strong>Bruno Manzoli</strong>
        <span>Não Está Gelando a sala</span>
        <span>Sala 06</span>
      </div>
    </div>

    <div class="coluna">
      <h3>Consertar 3 computadores</h3>
      <div class="card">
        <div class="tag amarelo"></div>
        <strong>Bruno Manzoli</strong>
        <span>3 pc não ta ligando</span>
        <span>Sala 06</span>
      </div>
    </div>
  `,

    Terminar: `
    <div class="coluna">
      <h3>Em andamento</h3>
      <div class="card">
        <div class="tag amarelo"></div>
        <strong>Bruno Manzoli</strong>
        <span>Não Está Gelando a sala</span>
        <span>Sala 06</span>
        <span>Finalizando o trabalho</span>
      </div>
    </div>
  `,

    Concluido: `
    <div class="coluna">
      <h3>Finalizados</h3>
      <div class="card">
        <div class="tag verde"></div>
        <strong>Bruno Manzoli</strong>
        <span>3 pc não ta ligando</span>
        <span>Sala 06</span>
        <span>Trabalho Finalizado</span>
      </div>
    </div>
  `
};

const links = document.querySelectorAll(".nav-link");

links.forEach(link => {
    link.addEventListener("click", function (event) {
        event.preventDefault();

        links.forEach(l => l.classList.remove("active"));
        link.classList.add("active");

        const textoLink = link.textContent.replace(">", "").trim();

        if (paginas[textoLink]) {
            conteudo.innerHTML = paginas[textoLink];
        }
    });
});