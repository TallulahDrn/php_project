document.addEventListener("DOMContentLoaded", function () {
    const button = document.querySelector("button[type='submit']");
    const formContainer = document.querySelector(".container");
    const emailInput = document.querySelector("#exampleInputEmail1");
    const problemTextArea = document.querySelector("#exampleInputPassword1");
    const navbar = document.querySelector(".navbar");

    // Fonction pour vérifier si les champs sont remplis
    function areFieldsFilled() {
        return emailInput.value.trim() !== "" && problemTextArea.value.trim() !== "";
    }

    // Écouteur de clic sur le bouton
    button.addEventListener("click", function (event) {
        // Si les champs ne sont pas remplis, on empêche l'action
        if (!areFieldsFilled()) {
            return;
        }

        // Obtenez la largeur et la hauteur du conteneur et de la navbar
        const containerRect = formContainer.getBoundingClientRect();
        const buttonRect = button.getBoundingClientRect();
        const navbarHeight = navbar.offsetHeight;  // Hauteur de la navbar

        // Calculez de nouvelles positions aléatoires, mais en s'assurant que le bouton ne sorte pas de l'écran
        const newLeft = Math.random() * (containerRect.width - buttonRect.width);
        const newTop = Math.random() * (containerRect.height - buttonRect.height - navbarHeight) + navbarHeight; // Ajustement pour éviter la navbar

        // Déplacez le bouton à la nouvelle position
        button.style.position = "absolute";
        button.style.left = `${newLeft}px`;
        button.style.top = `${newTop}px`;

        // Empêche le formulaire d'être envoyé (évite de le soumettre)
        event.preventDefault();
    });
});