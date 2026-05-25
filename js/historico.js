const botoes = document.querySelectorAll(".botao button");
const cards = document.querySelectorAll(".card");

botoes.forEach(botao => {
  botao.addEventListener("click", () => {

    const status = botao.dataset.status;

    cards.forEach(card => {
      if (status === "todos" || card.dataset.status === status) {
        card.style.display = "block";
      } else {
        card.style.display = "none";
      }
    });

  });
});