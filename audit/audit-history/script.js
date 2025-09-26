function searchAuditHistory() {
    var dept = document.getElementById("search").value;
    var offset = document.getElementById("offset").value;
    var type = document.getElementById("audit-type").value;

    if (dept === "") {
        dept = 'all';
    }
    console.log(dept);
    $.ajax({
        type: "POST",
        url: "audit-history.php",
        data: {
            search: dept,
            offset: offset,
            audit_type: type
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
    $('#audit-type').on('change', function(e) {
            e.preventDefault(); 
            searchAuditHistory();
    });
});
