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
    const profile_name_raw = document.getElementById('display-name').value;
    const profile_name = profile_name_raw.trim();
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
function deleteProfile(profile_name) {
    
    if (profile_name.length <= 0 || profile_name.trim() === '' || !profile_name) {
        return;
    }
    $.ajax({
        method: 'POST',
        url: 'profile-crud/remove-profile.php',
        data: {
            profile_name: profile_name
        },
        success: function () {
            console.log('Successfully removed profile', profile_name);
            displayProfiles();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
function adminDeleteProfile(data) {
    const array = data.split(" ").map(item => item.trim());
    const email = array[0];
    const profile_name = array[1];
    console.log(array);
    
    if (profile_name.length <= 0 || profile_name.trim() === '' || !profile_name) {
        return;
    }
    $.ajax({
        method: 'POST',
        url: 'profile-crud/admin-remove-profile.php',
        data: {
            email: email
            profile_name: profile_name
        },
        success: function () {
            console.log('Successfully removed profile', profile_name);
            displayProfiles();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("AJAX Error:", textStatus, errorThrown);
        }
    });
}
function renameProfile(new_name_raw, old_name) {
    if (new_name_raw.length <= 0 || new_name_raw.trim() === '') {
        return;
    }
    const new_name = new_name_raw.trim();
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
});
if (!window.rename_profile) {
    $(document).on('click', '.rename', function (e) {
        const row = $(this).closest('tr');
        const profile_name = row.find('input[type="text"]').val();
        renameProfile(profile_name, this.value);
    });
    window.rename_profile = true;
}

if (!window.delete_profile) {
    $(document).on('click', '.delete-profile', function (e) {
        deleteProfile(this.value);
    });
    window.delete_profile = true;
}

if (!window.admin_delete_profile) {
    $(document).on('click', '.admin-delete-profile', function (e) {
        adminDeleteProfile(this.value);
    });
    window.admin_delete_profile = true;
}

if (!window.audit_profile) {
    $(document).on('click', '.audit', function (e) {
        auditProfile(this.value);
    });
    window.audit_profile = true;
}
