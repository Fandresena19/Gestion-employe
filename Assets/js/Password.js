// Sélectionner tous les champs de mot de passe et icônes
const passwordFields = document.querySelectorAll('input[type="password"]');
const eyeIcons = document.querySelectorAll('.bx-hide, .bx-show');

// Ajouter un écouteur d'événement à chaque icône
eyeIcons.forEach((icon, index) => {
    icon.addEventListener('click', function() {
        // Trouver le champ de mot de passe correspondant (même parent)
        const passwordField = this.parentElement.querySelector('input');
        
        // Basculer entre password et text
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            this.className = 'bx bx-show';
        } else {
            passwordField.type = 'password';
            this.className = 'bx bx-hide';
        }
    });
});