function searchAuditHistory() {
    var dept = document.getElementById("search").value;
    var offset = document.getElementById("offset").value;

    if (dept === "") {
        dept = 'all';
    }
    console.log(dept);
    $.ajax({
        type: "POST",
        url: "audit-history.php",
        data: {
            search: dept,
            offset: offset
        },
        success: function(response) {
            console.log("Search results received.");

            $("#display").html(response);
        }
    });
}

$(document).ready(function() {
    $('#button-9').on('click', function() {
        searchAuditHistory();
    });
    $("#search").ready(searchAuditHistory); 
    $('#search').on('keypress', function(e) {
        if (e.which === 13) { 
            e.preventDefault(); 
            searchAuditHistory();
        }
    });
});
