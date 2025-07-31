function displayProfiles() {
    
    $.ajax({
        method: 'POST',
        url: 'display-profiles.php',
        success: function (html) {
            $('#display-profiles').html(html).show();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
function addProfile() {
    const profile_name = document.getElementById('display-name').value;
    console.log(profile_name, "before");
    if (profile_name.length <= 0) {
        return;
    }
    console.log(profile_name, "after");
    $.ajax({
        method: 'POST',
        url: 'profile-crud/add-profile.php',
        data: {
            profile_name: profile_name
        },
        success: function () {
            console.log('Successfully added profile');
            displayProfiles();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
function removeProfile() {
    const profile_name = document.getElementById('display-name').value;
    if (profile_name.length <= 0) {
        return;
    }
    $.ajax({
        method: 'POST',
        url: 'profile-crud/remove-profile.php',
        data: {
            profile_name: profile_name
        },
        success: function () {
            console.log('Successfully removed profile');
            displayProfiles();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
function renameProfile(new_name, old_name) {
    $.ajax({
        method: "POST",
        url: "profile-crud/rename-profile.php",
        data: {
            old_name: old_name,
            new_name: new_name
        },
        success: function () {
            console.log('Successfully renamed profile');
            displayProfiles();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
$(document).ready(function () {
    $('#add-profile').off('click').on('click', addProfile);
    $('#delete-profile').off('click').on('click', removeProfile);
    $('.rename').on('click', function (e) {
        const row = $(this).closest('tr');
        const profile_name = row.find('input[type="text"]').val();
        console.log('click');
        console.log(profile_name);
    });
});
$(document).on('click', '.rename', function (e) {
    const row = $(this).closest('tr');
    const profile_name = row.find('input[type="text"]').val();
    renameProfile(profile_name, this.value);
});
