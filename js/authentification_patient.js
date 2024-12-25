<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
    // Activation de la validation de Bootstrap
    (function () {
        'use strict'

        // Récupérer tous les formulaires auxquels on veut appliquer la validation
        var forms = document.querySelectorAll('.needs-validation')

        // Boucle à travers chaque formulaire
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated') // Ajout de la classe Bootstrap pour la validation
                }, false)
            })
    })()
</script>