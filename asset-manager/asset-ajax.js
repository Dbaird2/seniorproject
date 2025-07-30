function addAsset() {
    const input_val = document.getElementById('search-db').value;
    const profile_name = document.getElementById('profiles').value;
    $.ajax({
        method: 'POST',
        url: 'crud/asset-to-profile.php',
        data: {
            asset_tag: input_val,
            profile_name: profile_name
        },
        success: function () {
            console.log('add success');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
function deleteAsset() {
    const input_val = document.getElementById('search-db').value;
    const profile_name = document.getElementById('profiles').value;
    $.ajax({
        method: 'POST',
        url: 'crud/delete-asset.php',
        data: {
            asset_tag: input_val,
            profile_name: profile_name
        },
        success: function () {
            console.log('delete success');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });

}
function deleteAllAssets() {
    const profile_name = document.getElementById('profiles').value;
    $.ajax({
        method: 'POST',
        url: 'crud/profile-wipe.php',
        data: {
            profile_name: profile_name
        },
        success: function () {
            console.log('profile wipe success');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });

}
function quickStart() {
    const dept_id = document.getElementById('dept').value;
    const profile_name = document.getElementById('profiles').value;
    $.ajax({
        method: 'POST',
        url: 'crud/quick-start-profile.php',
        data: {
            dept_id: dept_id,
            profile_name: profile_name
        },
        success: function () {
            console.log('add success');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });

}
function displayTable() {
    const profile_name = document.getElementById('profiles').value;
    $.ajax({
        method: 'POST',
        url: "sheet.php",
        data: {
            status: 1,
            profile_name: profile_name
        },
        success: function (html) {
            $("#display-table").html(html).show();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
$(document).ready(function () {
    //$('#search-db').off('change').on('change', addAsset);
    $('#add-asset').off('click').on('click', addAsset);
    $('#load-profile').off('click').on('click', displayTable);
    $('#').off('click').on('click', deleteAsset);
    $('#restart').off('click').on('click',deleteAllAssets);
    $('#quick-start').off('click').on('click', quickStart);
});
