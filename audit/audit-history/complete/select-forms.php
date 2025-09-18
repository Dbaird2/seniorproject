
<?php
include_once "../../../config.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Extra Tags Audit</title>
<?php include_once "../../../navbar.php"; ?>
</head>
<body>

<table class="table">
    <thead>
        <tr>
            <th></th>
            <th>Unit</th>
            <th>Unit</th>
            <th>Asset Tag</th>
            <th>Description</th>
            <th>Tag Status</th>
            <th>Audit Note</th>
            <th>Form</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($_SESSION['data'] as $index => $row) { ?>
        <tr>
        <th><input type="checkbox" class="checkboxes" value"<?= $row['Tag Number'] ?>"></th>
            <th><?= $row['Unit'] ?></th>
            <th><?= $row['Tag Number'] ?></th>
            <th><?= $row['Descr'] ?></th>
            <th><?= $row['Tag Status'] ?></th>
            <th><?= $row['Found Note'] ?></th>
            <th>
                <select name="form-type" id="form-<?=$row['Tag Number']?>">
                    <option value="bulk-transfer">Bulk Transfer</option>
                    <!-- <option value="transfer">Transfer</option> -->
                </select>
            </th>
        </tr>
<?php } ?>
    </tbody>
</table>
<button id="submit">Submit Bulk Transfer</button>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btn = document.getElementById("submit").addEventListener("click", function () {
            const tags = [];
            const checkboxes = document.querySelectorAll('.checkboxes');
            checkboxes.forEach(checkbox => {
                if (checkbox.check) {
                    tags.push(checkbox.value);
                }
            });
            url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/bulk-transfer.php";
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(tags)
            })
                .then(response => response.json())
                .then(result => console.log(result));
        });
    });
</script>
</body>
</html>
    
    

