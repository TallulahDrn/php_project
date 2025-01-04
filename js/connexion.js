// Vérifier si un paramètre 'error' existe dans l'URL

const urlParams = new URLSearchParams(window.location.search);
const errorMessage = urlParams.get('error');

// Si un message d'erreur est trouvé, afficher une alerte
if (errorMessage) {
    alert(errorMessage);
}
    


/*
// Vérifier si un paramètre 'error' existe dans l'URL
const urlParams = new URLSearchParams(window.location.search);
const errorMessage = urlParams.get('error');

// Si un message d'erreur est trouvé, afficher le modal
if (errorMessage) {
    // Insérer le message d'erreur dans le modal
    document.getElementById('errorMessage').innerText = errorMessage;
    
    // Afficher le modal Bootstrap
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
}


// Vérifier si un paramètre 'error' existe dans l'URL
const urlParams = new URLSearchParams(window.location.search);
const errorMessage = urlParams.get('error');

// Si un message d'erreur est trouvé, afficher dans le div
if (errorMessage) {
    // Insérer le message d'erreur dans le div
    document.getElementById('errorMessage').innerText = errorMessage;
    
    // Afficher le div contenant l'erreur
    document.getElementById('errorMessageContainer').style.display = 'block';
}
*/