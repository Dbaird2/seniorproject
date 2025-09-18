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
            <th><?= $row['Unit'] ?></th>
            <th><?= $row['Tag Number'] ?></th>
            <th><?= $row['Descr'] ?></th>
            <th><?= $row['Tag Status'] ?></th>
            <th><?= $row['Found Note'] ?></th>
            <th>
            <select name="form-type" id="form-<?=$row['Tag Number']?>" data-tag="<?= $row['Tag Number'] ?>" class="forms-needed">
                     <option value="">No Form Needed</option>
                     <option value="bulk-transfer" >Bulk Transfer</option>
                     <option value="transfer">Transfer</option>
                     <option value="bulk-psr">Bulk Property Survey Report</option>
                     <option value="psr">Property Survey Report</option>
                     <option value="check-out">Check Out</option>
                     <option value="check-in" >Check In</option>
                </select>
            </th>
        </tr>
<?php } ?>
    </tbody>
</table>
<button id="submit">Submit Forms</button>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const btn = document.getElementById("submit").addEventListener("click", async () => {
            let bulk_t_tags =  [], t_tags = [], b_psr_tags = [], psr_tags = [], out_tags = [], in_tags = [];
            const forms_needed = document.querySelectorAll('.forms-needed');

            forms_needed.forEach((type) => {
                const val = type.value;
                if (val == 'bulk-transfer') {
                    bulk_t_tags.push(type.dataset.tag);
                } else if (val == 'transfer') {
                    t_tags.push(type.dataset.tag);
                } else if (val == 'bulk-psr') {
                    bulk_psr_tags.push(type.dataset.tag);
                } else if (val == 'psr') {
                    psr_tags.push(type.dataset.tag);
                } else if (val == 'check-out') {
                    out_tags.push(type.dataset.tag);
                } else if (val == 'check-in') {
                    in_tags.push(type.dataset.tag);
                } 
            });
        /*
            url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/check-out.php";
            const out_res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ out_tags })
            });
            if (!out_res.ok) {
                const text = await out_res.text();
                throw new Error (`HTTP ${out_res.status}: ${text}`);
            } else {
                console.log(out_res);
            }
            const in_res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ in_tags })
            });
            if (!in_res.ok) {
                const text = await in_res.text();
                throw new Error (`HTTP ${in_res.status}: ${text}`);
            } else {
                console.log(in_res);
            }
            url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/psr.php";
            const psr_res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ psr_tags })
            });
            if (!psr_res.ok) {
                const text = await psr_res.text();
                throw new Error (`HTTP ${psr_res.status}: ${text}`);
            } else {
                console.log(psr_res);
            }
            url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/bulk-psr.php";
            const bpsr_res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bulk_psr_tags })
            });
            if (!bpsr_res.ok) {
                const text = await bpsr_res.text();
                throw new Error (`HTTP ${bpsr_res.status}: ${text}`);
            } else {
                console.log(bpsr_res);
            }
            url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/transfer.php";
            const t_res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ t_tags })
            });
            if (!t_res.ok) {
                const text = await t_res.text();
                throw new Error (`HTTP ${t_res.status}: ${text}`);
            } else {
                console.log(t_res);
            }
            */   
        if (bulk_t_tags.length !== 0) {
            url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/bulk-transfer.php";
            const bulk_t_res = await fetch(url, {
            method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bulk_t_tags })
            });
            if (!bulk_t_res.ok) {
                const text = await bulk_t_res.text();
                throw new Error (`HTTP ${bulk_t_res.status}: ${text}`);
            } else {
                console.log(bulk_t_res);
            }
        }
        });
    });
</script>
</body>
</html>
    
    

