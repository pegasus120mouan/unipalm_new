$(document).ready(function() {
    // Fonction pour afficher le spinner
    function showSpinner() {
        $('#resultsTable').hide();
        $('#loadingSpinner').show();
    }

    // Fonction pour cacher le spinner
    function hideSpinner() {
        $('#loadingSpinner').hide();
        $('#resultsTable').show();
    }

    // Gérer le changement des filtres
    $('#searchForm select, #searchForm input[type="date"]').on('change', function() {
        showSpinner();
        $('#searchForm').submit();
    });

    // Gérer la soumission du formulaire
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        showSpinner();

        $.get('recherche_trie.php?' + $(this).serialize(), function(response) {
            // Extraire la table des résultats de la réponse
            var tempDiv = $('<div>').html(response);
            var newTable = tempDiv.find('#resultsTable').html();
            
            // Mettre à jour la table
            $('#resultsTable').html(newTable);
            hideSpinner();
        });
    });
});
