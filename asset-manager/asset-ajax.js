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
    console.log(input_val);
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
    console.log('deleting ', profile_name);
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
        success: function (data) {
            if (data.status === 'success') {
                console.log('add-note success', data.message);
            } else {
                console.log(data.message);
            }
                
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });

}
function download_file(type) {
    const profile_name = document.getElementById('profiles').value;
    const download = (type === 'pdf') ? 'pdf' : 'excel';
    const url = (type === 'pdf') ? 'crud/pdf-download.php' : 'crud/excel-download.php';
    const pdf = true;
    $.ajax ({
        method: 'POST',
        url: url,
        data: {
            profile_name: profile_name,
            type: download
        },
        success : function () {
            console.log('downloading pdf');
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
if (!window.assetDeleteBound) {
    $(document).off('click.asset-row').on('click.asset-row', '.asset-row', function(e) {
        const asset_tag = this.value;
        deleteAsset(asset_tag);
    });
    window.assetDeleteBound= true;
}
if (!window.assetNoteBound) {
    $(document).off('blur.asset-note').on('blur.asset-note','.asset-note', function(e) {
        const note = this.value;
        const tag = this.id;
        addNote(note, tag);
    });
    window.assetNoteBound= true;
}
