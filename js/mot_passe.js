document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(event.target);

            try {
                const response = await fetch(event.target.action, {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error(`Erreur réseau : ${response.statusText}`);
                }

                const result = await response.json();
                console.log("Réponse serveur :", result);

                const modalMessage = document.getElementById('modalMessage');
                if (result.status === 'success') {
                    modalMessage.innerHTML = `<p class="text-success">${result.message}</p>`;
                    // Redirection vers la page de réinitialisation
                    setTimeout(() => {
                        const email = document.getElementById('email').value; // Récupérer l'email du formulaire
                        window.location.href = '/Projet_php_2/php_project/php/reinitialisation_mdp.php?email=${encodeURIComponent(email)}'; // Redirige vers la page PHP 
                    }, 2000);
                } else {
                    modalMessage.innerHTML = `<p class="text-danger">${result.message}</p>`;
                }

                const modal = new bootstrap.Modal(document.getElementById('resultModal'));
                modal.show();

            } catch (error) {
                console.error("Erreur rencontrée :", error);

                const modalMessage = document.getElementById('modalMessage');
                modalMessage.innerHTML = `<p class="text-danger">Une erreur est survenue. Veuillez réessayer.</p>`;
                const modal = new bootstrap.Modal(document.getElementById('resultModal'));
                modal.show();
            }
        });
    } else {
        console.error("Le formulaire n'a pas été trouvé dans le DOM.");
    }
});
