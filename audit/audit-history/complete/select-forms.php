<?php
include_once "../../../config.php";
check_auth();
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
            <td><?= $row['Unit'] ?></td>
            <td><?= $row['Tag Number'] ?></td>
            <td><?= $row['Descr'] ?></td>
            <td><?= $row['Tag Status'] ?></td>
            <td><?= $row['Found Note'] ?></td>
            <td>
            <select name="form-type" id="form-<?=$row['Tag Number']?>" data-tag="<?= $row['Tag Number'] ?>" class="forms-needed">
                     <option value="">No Form Needed</option>
                    <?php if (in_array($_SESSION['role'], ['admin', 'management'])) { ?>
                     <option value="bulk-transfer" >Bulk Transfer</option>
                    <?php } ?>
                     <option value="psr">Property Survey Report</option>
                     <option value="psr">Equipment Loss/Stolen/Destroyed</option>
                     <option value="check-out">Check Out</option>
                     <option value="check-in" >Check In</option>
                </select>
            </td>
<!--PSR INFO FROM USER 
    REASON FOR DISPOSTAL
    DISPOSITION CODE
    -->
    <td class="psr-<?= $row['Tag Number']?>" style="display:none;"><select id="psr-code-<?=$row['Tag Number'] ?>"> 
            <option value=""/>
            <option value="UNIVERSAL WASTE — SALVAGE DEALER, RECYCLER (E-WASTE)"/>
            <option value="VALUELESS UNABLE TO BE RECYCLED (TO BE LEGALLY/SAFELY DISPOSED OF)"/>
            <option value="SHIPPED TO SCRAP / SALVAGE DEALER (TO BE RECYCLED) NOTE: FOR E-WASTE USE # 10"/>
        </select>
            </td>
            <td class="psr-<?= $row['Tag Number']?>" style="display:none;"><select id="psr-reason-<?=$row['Tag Number'] ?>">
                    <option value="" />
</select>
</td>
<!--EQUIP LSD INFO FROM USER
    MYSELF OR SOMEONE ELSE
    STAFF/FACULTY OR STUDENT
    NAME OF PERSON FILLING OUT FOR
    LOST STOLEN OR DESTROYED
    DETAILED NARRATIVE
    REPORTED TO PUBLIC SAFETY
     -->
     <td class="lsd-<?= $row['Tag Number']?>" style="display:none;"><select id="lsd-who-<?=$row['Tag Number'] ?>">
            <option value="" />
            <option value="Myself" />
            <option value="I am initiating this submission on behalf of" />
</select>
</td>
<td class="lsd-<?= $row['Tag Number']?>" style="display:none;">
<select id="lsd-position-<?=$row['Tag Number'] ?>">
<option value="" />
<option value="Staff/Faculty" />
<option value="Student" />
</select>
</td>
<td class="lsd-<?= $row['Tag Number']?>" style="display:none;"><input type="text" id="lsd-fill-for-<?=$row['Tag Number'] ?>"></td>
<td class="lsd-<?= $row['Tag Number']?>" style="display:none;"><select id="lsd-type-<?=$row['Tag Number']?>">
        <option value="">
        <option value="Lost">
        <option value="Stolen">
        <option value="Destroyed">
</select></td>
<td class="lsd-<?= $row['Tag Number']?>" style="display:none;"><textarea type="text" id="lsd-narrative-<?=$row['Tag Number']?>" placeholder="Detail Narrative" /></td>
<td class="lsd-<?= $row['Tag Number']?>" style="display:none;"><select id="lsd-report-<?=$row['Tag Number']?>">
        <option value="">
        <option value="Yes">
        <option value="No">
    </select>
</td>
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
        url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/check-out.php";
        const check_type = document.getElementById('check-type-'+type.dataset.tag).value;
        const borrower = document.getElementById('check-borrower-'+type.dataset.tag).value;
        const condition = document.getElementById('check-condition-'+type.dataset.tag).value;
        const notes = document.getElementById('check-notes-'+type.dataset.tag).value;
        const out_res = await fetch(url, {
        method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ form_type: 'out',
                                    tag: type.dataset.tag,
                                    type: check_type,
                                    borrower: borrower,
                                    condition: condition,
                                    notes: notes })
        });
        if (!out_res.ok) {
            const text = await out_res.text();
            throw new Error (`HTTP ${out_res.status}: ${text}`);
        } else {
            const clone = out_res.clone();
            try {
                const data = await out_res.json();
                console.log("Check out response (JSON):", data);
            } catch {
                const text = await clone.text();  
                console.log("Check out response (text):", text);
            }
        }
    } else if (val == 'check-in') {
        url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/check-out.php";
        const check_type = document.getElementById('check-type-'+type.dataset.tag).value;
        const borrower = document.getElementById('check-borrower-'+type.dataset.tag).value;
        const condition = document.getElementById('check-condition-'+type.dataset.tag).value;
        const notes = document.getElementById('check-notes-'+type.dataset.tag).value;
            const in_res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    form_type: 'in', 
                    tag: type.dataset.tag,
                    type: check_type,
                    borrower: borrower,
                    condition: condition,
                    notes: notes })
        });
        if (!in_res.ok) {
                const text = await in_res.text();
                throw new Error (`HTTP ${in_res.status}: ${text}`);
            } else {
                const clone = in_res.clone();
                try {
                    const data = await in_res.json();
                    console.log("Check in response (JSON):", data);
                } catch {
                    const text = await clone.text();  
                    console.log("Check in response (text):", text);
                }
            }
    } 
    });
    console.log(bulk_t_tags);
            /*
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
            throw new Error(`HTTP ${bulk_t_res.status}: ${text}`);
        } else {
            const clone = bulk_t_res.clone();
            try {
                const data = await bulk_t_res.json();
                console.log("bulk-transfer response (JSON):", data);
            } catch {
                const text = await clone.text();  
                console.log("bulk-transfer response (text):", text);
            }
        }
    }
});
});
</script>
</body>
</html>
    
    

