</div>
  </div>
  <script>
  function confirmDelete(event, url) {
    event.preventDefault(); // Empêche le lien de se comporter comme un lien normal

    // Affiche le modal
    document.getElementById('confirmationModal').style.display = 'block';

    // Gestion de la confirmation
    document.getElementById('confirmDelete').onclick = function() {
      window.location.href = url; // Redirige vers l'URL de suppression
    };

    // Gestion de l'annulation
    document.getElementById('cancelDelete').onclick = function() {
      document.getElementById('confirmationModal').style.display = 'none'; // Cache le modal
    };
  }
</script>
  <script src="../js/Sidebar.js"></script>
</body>

</html>