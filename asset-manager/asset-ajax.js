function displayUpdatedTable(profile_name) {
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
            displayUpdatedTable(profile_name);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
function deleteAsset(asset_tag) {
    const profile_name = document.getElementById('profiles').value;
    $.ajax({
        method: 'POST',
        url: 'crud/delete-asset.php',
        data: {
            asset_tag: asset_tag,
            profile_name: profile_name
        },
        success: function () {
            console.log('delete success');
            displayUpdatedTable(profile_name);
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
            displayUpdatedTable(profile_name);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });

}
function quickStart() {
    const dept_name = document.getElementById('dept').value;
    const profile_name = document.getElementById('profiles').value;
    $.ajax({
        method: 'POST',
        url: 'crud/quick-start-profile.php',
        data: {
            dept_name: dept_name,
            profile_name: profile_name
        },
        success: function () {
            console.log('add success');
            displayUpdatedTable(profile_name);
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
function addNote(note, tag) {
    const profile_name = document.getElementById('profiles').value;
    $.ajax({
        method: 'POST',
        url: "crud/add-note.php",
        data: {
            asset_note: note,
            asset_tag: tag,
            profile_name: profile_name
        },
        success: function () {
            console.log('add-note success');
            displayUpdatedTable(profile_name);
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
    $('#restart').off('click').on('click',deleteAllAssets);
    $('#quick-start').off('click').on('click', quickStart);
});
    $(document).on('click', '.asset-row', function(e) {
        const asset_tag = this.value;
        deleteAsset(asset_tag);
    });
    $(document).on('blur','.asset-note', function(e) {
            const note = this.value;
            const tag = this.id;
            addNote(note, tag);
    });
