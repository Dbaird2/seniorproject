<?php
include_once "../../../config.php";
check_auth();
$select_dept = "SELECT dept_name FROM department WHERE dept_id = :id";
$stmt = $dbh->prepare($select_dept);
$stmt->execute([":id" => $_SESSION['info'][2]]);
$dept_name = $stmt->fetchColumn();
$audit_id = $_SESSION['info'][5];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Form Submissions <?= $_SESSION['info'][2] . ' ' . $dept_name ?></title>
    <?php include_once "../../../navbar.php"; ?>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%);
            margin: 0;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 2rem;
        }

        .header {
            background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
        }

        .table-wrapper {
            margin-top: 0;
            overflow-x: auto;
            padding: 1.5rem;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9rem;
        }

        .table thead tr {
            background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
            color: white;
        }

        .table thead th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table thead th:first-child {
            border-top-left-radius: 8px;
        }

        .table thead th:last-child {
            border-top-right-radius: 8px;
        }

        .table tbody tr {
            background: white;
            transition: all 0.3s ease;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody tr:hover {
            background: #f0f9ff;
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(66, 165, 245, 0.1);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table tbody td:not([style*="display: none"]) {
            opacity: 1;
            animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        select,
        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 2px solid #e3f2fd;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            font-family: inherit;
        }

        select:focus,
        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #42a5f5;
            box-shadow: 0 0 0 3px rgba(66, 165, 245, 0.15);
        }

        select:hover,
        input[type="text"]:hover,
        textarea:hover {
            border-color: #90caf9;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }

        .forms-needed {
            background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
            color: white;
            font-weight: 500;
            cursor: pointer;
        }

        .forms-needed option {
            background: white;
            color: #2d3748;
        }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #1565c0;
            margin-bottom: 0.375rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        #submit {
            background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
            color: white;
            border: none;
            padding: 1rem 3rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            margin: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(66, 165, 245, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        #submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 165, 245, 0.6);
        }

        #submit:active {
            transform: translateY(0);
        }

        .form-field-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-field-group[style*="display: none"] {
            opacity: 0;
            transform: translateY(-10px);
        }

        .form-field-group:not([style*="display: none"]) {
            opacity: 1;
            transform: translateY(0);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .table {
                font-size: 0.8rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Form Submissions - <?= $_SESSION['info'][2] . ' ' . $_SESSION['info'][3] ?></h1>
        </div>
        <div class="table-wrapper">
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
                        <tr class="row-<?= $row['Tag Number'] ?>">
                            <td style="font-weight: 700;background-color: #e5F3Fd;"><?= $row['Unit'] ?></td>
                            <td style="font-weight: 600;background-color: #e5F3Fd;"><?= $row['Tag Number'] ?></td>
                            <td style="font-weight: 600;background-color: #e5F3Fd;"><?= $row['Descr'] ?></td>
                            <td style="font-weight: 600;background-color: #e5F3Fd;"><?= $row['Tag Status'] ?></td>
                            <td style="font-weight: 600;background-color: #e5F3Fd;"><?= $row['Found Note'] ?></td>
                            <td style="font-weight: 600;background-color: #e5F3Fd;">
                                <select name="form-type" id="form-<?= $row['Tag Number'] ?>" data-tag="<?= $row['Tag Number'] ?>" class="forms-needed">
                                    <option value="">No Form Needed</option>
                                    <?php if (in_array($_SESSION['role'], ['admin', 'management'])) { ?>
                                        <option value="bulk-transfer">Bulk Transfer</option>
                                    <?php } ?>
                                    <option value="psr">Property Survey Report</option>
                                    <option value="lsd">Equipment Loss/Stolen/Destroyed</option>
                                    <option value="check-out">Check Out</option>
                                    <option value="check-in">Check In</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="psr-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Disposition Code</label>
                                    <select id="psr-code-<?= $row['Tag Number'] ?>">
                                        <option value="UNIVERSAL WASTE — SALVAGE DEALER, RECYCLER (E-WASTE)">UNIVERSAL WASTE — SALVAGE DEALER, RECYCLER (E-WASTE)</option>
                                        <option value="VALUELESS UNABLE TO BE RECYCLED (TO BE LEGALLY/SAFELY DISPOSED OF)">VALUELESS UNABLE TO BE RECYCLED (TO BE LEGALLY/SAFELY DISPOSED OF)</option>
                                        <option value="SHIPPED TO SCRAP / SALVAGE DEALER (TO BE RECYCLED) NOTE: FOR E-WASTE USE # 10">SHIPPED TO SCRAP / SALVAGE DEALER (TO BE RECYCLED) NOTE: FOR E-WASTE USE # 10</option>
                                        <option value"LOST, STOLEN OR DESTROYED (REFER TO SAM SECTION 8643 FOR INSTRUCTIONS)">LOST, STOLEN OR DESTROYED (REFER TO SAM SECTION 8643 FOR INSTRUCTIONS)</option>
                                        <option value="TO BE CANABALIZED (SALVAGED FOR PARTS)">TO BE CANABALIZED (SALVAGED FOR PARTS)</option>
                                        <option value="DONATION TO AN ELIGIBLE PUBLIC SCHOOL, PUBLIC SCHOOL DISTRICT OR ELIGIBLE ORGANIZATION  (SEE SAM SECTION 3520.5)">DONATION TO AN ELIGIBLE PUBLIC SCHOOL, PUBLIC SCHOOL DISTRICT OR ELIGIBLE ORGANIZATION (SEE SAM SECTION 3520.5)</option>
                                        <option value="SHIP TO PROPERTY REUSE PROGRAM (NO POOR OR JUNK MATERIAL)">SHIP TO PROPERTY REUSE PROGRAM (NO POOR OR JUNK MATERIAL)</option>
                                        <option value="DONATION OF COMPUTERS FOR SCHOOLS PROGRAM">DONATION OF COMPUTERS FOR SCHOOLS PROGRAM</option>
                                        <option value="SALE (SEE SAM SECTION 3520)">SALE (SEE SAM SECTION 3520)</option>
                                        <option value="TRADE-IN (SHOW TRADE-IN PRICE OFFERED)">TRADE-IN (SHOW TRADE-IN PRICE OFFERED)</option>
                                    </select>
                                </div>
                            </td>
                            <td class="psr-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Reason for Disposal</label>
                                    <input type="text" id="psr-reason-<?= $row['Tag Number'] ?>" placeholder="Enter reason...">
                                </div>
                            </td>
                            <?php if (!in_array((int)$audit_id, [4, 5, 6])) { ?>
                                <td class="lsd-<?= $row['Tag Number'] ?>" style="display:none;">
                                    <div class="form-field-group">
                                        <label>Submitting For</label>
                                        <select id="lsd-who-<?= $row['Tag Number'] ?>">
                                            <option value="Myself">Myself</option>
                                            <option value="someone-else">Someone Else</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="lsd-fill-<?= $row['Tag Number'] ?>" style="display:none;">
                                    <div class="form-field-group">
                                        <label>Borrower Email</label>
                                        <input type="text" id="lsd-fill-for-<?= $row['Tag Number'] ?>" placeholder="Enter email...">
                                    </div>
                                </td>
                                <td class="lsd-<?= $row['Tag Number'] ?>" style="display:none;">
                                    <div class="form-field-group">
                                        <label>Position</label>
                                        <select id="lsd-position-<?= $row['Tag Number'] ?>">
                                            <option value="Staff/Faculty">Staff/Faculty</option>
                                            <option value="Student">Student</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="lsd-<?= $row['Tag Number'] ?>" style="display:none;">
                                    <div class="form-field-group">
                                        <label>Condition</label>
                                        <select id="lsd-condition-<?= $row['Tag Number'] ?>">
                                            <option value="Lost">Lost</option>
                                            <option value="Stolen">Stolen</option>
                                            <option value="Destroyed">Destroyed</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="lsd-<?= $row['Tag Number'] ?>" style="display:none;">
                                    <div class="form-field-group">
                                        <label>Detailed Narrative</label>
                                        <textarea id="lsd-narrative-<?= $row['Tag Number'] ?>" placeholder="Provide detailed description..."></textarea>
                                    </div>
                                </td>
                                <td class="lsd-<?= $row['Tag Number'] ?>" style="display:none;">
                                    <div class="form-field-group">
                                        <label>Reported to UPD?</label>
                                        <select id="upd-<?= $row['Tag Number'] ?>">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="lsd-<?= $row['Tag Number'] ?>" style="display:none;">
                                    <div class="form-field-group">
                                        <label>Item Type</label>
                                        <select id="item-type-<?= $row['Tag Number'] ?>">
                                            <option value=""></option>
                                            <option value="IT Equipment">IT Equipment</option>
                                            <option value="Instructional Equipment">Instructional Equipment</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </td>
                        </tr>
                        <tr>
                            <td class="lsd-it-equip-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Describe the item lost</label>
                                    <input type="text" 
                                        id="upd-describe-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-it-equip-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Did this equipment have any confidential information stored on it?</label>
                                    <select id="lsd-it-equip-confidential-<?= $row['Tag Number'] ?>">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                            </td>
                            <td class="lsd-it-equip-confidential-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Describe as completely as possible the nature of the confideial data that was stored on this equipment</label>
                                    <input type="text" placeholder="i.e. Names, Social Security Number's, Date of Bird, Driver License #'s, Credit Card #'s, etc"
                                        id="lsd-it-equip-confidential-input-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-it-equip-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Was the confidential data stored on this asset encrypted and/or password protected?</label>
                                    <select id="lsd-it-equip-encrypted-<?= $row['Tag Number'] ?>">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                            </td>
                            <td class="lsd-it-equip-encrypted-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Please describe how the data was protected</label>
                                    <input type="text" id="lsd-it-equip-encrypted-input-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Date Reported</label>
                                    <input type="date" id="upd-date-reported-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Time Reported</label>
                                    <input type="time" id="upd-time-reported-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Date Last Seen</label>
                                    <input type="date" id="upd-date-last-seen-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Time Last Seen</label>
                                    <input type="time" id="upd-time-last-seen-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>By Whom?</label>
                                    <input type="text" id="upd-by-whom-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Location</label>
                                    <input type="text" id="upd-location-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Was the area/room secured?</label>
                                    <select id='upd-secured-<?=$row['Tag Number']?>'>
                                        <option value='No'>No</option>
                                        <option value='Yes'>Yes</option>
                                    </select>
                                </div>
                            </td>
                            <td class="lsd-upd-access-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Who has access keys?</label>
                                    <input type="text" id="upd-access-keys-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Does your department have and assigned staff member responsible for equipment?</label>
                                    <select id="upd-assigned-staff-<?= $row['Tag Number'] ?>">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                            </td>
                            <td class="lsd-upd-yes-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Who?</label>
                                    <input type="text" id="upd-who-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>What steps were taken to recover the asset?</label>
                                    <input type="text" id="upd-recovery-steps-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>What precautions are in effect to prevent loss or theft?</label>
                                    <input type="text" id="upd-precautions-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>If this equipment was used off-campus who authorized its use?</label>
                                    <input type="text" id="upd-authorized-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>What security provided off campus?</label>
                                    <input type="text" id="upd-security-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Was the loss or theft reported to local authorities?</label>
                                    <select id="upd-reported-<?= $row['Tag Number'] ?>">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                            </td>
                            <td class="lsd-upd-explain-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Explain</label>
                                    <input type="text" id="upd-explain-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="lsd-upd-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Do you have insurance?</label>
                                    <select id="upd-insurance-<?= $row['Tag Number'] ?>">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                            </td>
                            <td class="lsd-upd-insurance-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Company Name</label>
                                    <input type="text" id="upd-company-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-insurance-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Street</label>
                                    <input type="text" id="upd-street-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-insurance-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>City</label>
                                    <input type="text" id="upd-city-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-insurance-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>Zip Code</label>
                                    <input type="text" id="upd-zip-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                            <td class="lsd-upd-insurance-<?= $row['Tag Number'] ?>" style="display:none;">
                                <div class="form-field-group">
                                    <label>State</label>
                                    <input type="text" id="upd-state-<?= $row['Tag Number'] ?>">
                                </div>
                            </td>
                        <?php } ?>
                        <td class="check-out-<?= $row['Tag Number'] ?> check-in-<?= $row['Tag Number'] ?>" style="display:none;">
                            <div class="form-field-group">
                                <label>Checking Out For</label>
                                <select class="who-<?= $row['Tag Number'] ?>">
                                    <option value="Myself">Myself</option>
                                    <option value="someone-else">Someone Else</option>
                                </select>
                            </div>
                        </td>
                        <td class="check-out-<?= $row['Tag Number'] ?> check-in-<?= $row['Tag Number'] ?>" style="display:none;">
                            <div class="form-field-group someone-else-<?= $row['Tag Number'] ?>" style="display:none;">
                                <label>Borrower Name</label>
                                <input type="text" name="full-name" id="full-name" placeholder="Full name of borrower">
                            </div>
                        </td>
                        <td class="check-out-<?= $row['Tag Number'] ?> check-in-<?= $row['Tag Number'] ?>" style="display:none;">
                            <div class="form-field-group">
                                <label>Condition</label>
                                <select id="check-condition-<?= $row['Tag Number'] ?>">
                                    <option value="new">New</option>
                                    <option value="good">Good</option>
                                    <option value="used">Used</option>
                                    <option value="damanged">Damaged</option>
                                </select>
                            </div>
                        </td>
                        <td class="check-out-<?= $row['Tag Number'] ?> check-in-<?= $row['Tag Number'] ?>" style="display:none;">
                            <div class="form-field-group">
                                <label>Notes</label>
                                <textarea id="check-notes-<?= $row['Tag Number'] ?>" placeholder="Additional notes..."></textarea>
                            </div>
                        </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div style="text-align: center;">
            <button id="submit">Submit Forms</button>
        </div>
    </div>
<script>
const document_audit_id = parseInt(<?= json_encode([$audit_id]) ?>);

function hideUI(type, tag) {
    const form = document.querySelectorAll('.' + type + '-' + tag);
    form.forEach(el => {
    el.style.display = 'none';
    });
    return;
}

function showUI(type, tag) {
    const form = document.querySelectorAll('.' + type + '-' + tag);
    form.forEach(el => {
        el.style.display = 'table-cell';
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const dept_id = <?= json_encode($_SESSION['info'][2], true) ?>;
    const audit_id = <?= json_encode($_SESSION['info'][3], true) ?>;
    const forms_needed = document.querySelectorAll('.forms-needed');

    forms_needed.forEach(form_type => {
    form_type.addEventListener('change', () => {
    console.log('Changed input', form_type, form_type.value);
    const tag = form_type.dataset.tag;
    console.log(form_type.value, tag);
    if (form_type.value === '') {
            hideUI('lsd-it-equip', tag);
        hideUI('lsd-upd-explain', tag);
        hideUI('lsd-upd-access', tag);
        hideUI('check-out', tag);
        hideUI('check-in', tag);
        hideUI('lsd', tag);
        hideUI('psr', tag);
        hideUI('bulk-transfer', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd-insurance', tag);
        hideUI('lsd-fill-', tag);
        return;
    }
    const form_class = document.querySelectorAll('.' + form_type.value + '-' + form_type.dataset.tag);
    if (form_type.value === 'check-out') {
        someone_else = document.querySelector('.who-' + tag);
        someone_else.addEventListener('change', () => {
        console.log(someone_else.value);
        if (someone_else.value === 'someone-else') {
            document.querySelector('.someone-else-' + tag).style.display = 'block';
        } else {
            document.querySelector('.someone-else-' + tag).style.display = 'none';
        }
        });
                hideUI('lsd-upd-explain', tag);
                hideUI('lsd-upd-access', tag);
            hideUI('lsd-it-equip', tag);
        hideUI('check-in', tag);
        hideUI('lsd', tag);
        hideUI('psr', tag);
        hideUI('bulk-transfer', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd-insurance', tag);
        hideUI('lsd-fill-', tag);
    }

    if (form_type.value === 'check-in') {
        const someone_else = document.querySelector('.who-' + tag);
        someone_else.addEventListener('change', () => {
        console.log(someone_else.value);
        if (someone_else.value === 'someone-else') {
            document.querySelector('.someone-else-' + tag).style.display = 'block';
        } else {
            document.querySelector('.someone-else-' + tag).style.display = 'none';
        }
        });
                hideUI('lsd-upd-explain', tag);
                hideUI('lsd-upd-access', tag);
            hideUI('lsd-it-equip', tag);
        hideUI('check-out', tag);
        hideUI('lsd', tag);
        hideUI('psr', tag);
        hideUI('bulk-transfer', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd-insurance', tag);
        hideUI('lsd-fill-', tag);
    }

    if (form_type.value === 'psr') {
                hideUI('lsd-upd-explain', tag);
                hideUI('lsd-upd-access', tag);
            hideUI('lsd-it-equip', tag);
        hideUI('check-out', tag);
        hideUI('lsd', tag);
        hideUI('check-in', tag);
        hideUI('bulk-transfer', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd-insurance', tag);
        hideUI('lsd-fill-', tag);
    }

    if (form_type.value === 'lsd' && (document_audit_id !== 4 && document_audit_id !== 5 && document_audit_id !== 6)) {
        const upd = document.getElementById('upd-' + tag);
        upd.addEventListener('change', () => {
            console.log(upd.value);
            if (upd.value === 'Yes') {
                showUI('lsd-upd', tag);
                const assigned = document.getElementById('upd-assigned-staff-' + tag);
                assigned.addEventListener('change', () => {
                    if (assigned.value === 'Yes') {
                        showUI('lsd-upd-yes', tag);
                    } else {
                        hideUI('lsd-upd-yes', tag);
                    }
                });
                const insurance = document.getElementById('upd-insurance-' + tag);
                insurance.addEventListener('change', () => {
                    if (insurance.value === 'Yes') {
                        showUI('lsd-upd-insurance', tag);
                    } else {
                        hideUI('lsd-upd-insurance', tag);
                    }
                });
                const secured = document.getElementById('upd-secured-'+tag);
                secured.addEventListener('change', () => {
                    if (secured.value === 'Yes') {
                        showUI('lsd-upd-access', tag);
                    } else {
                        hideUI('lsd-upd-access', tag);
                    }
                });
                const local = document.getElementById('upd-reported-'+tag);
                local.addEventListener('change', () => {
                    if (local.value === 'Yes') {
                        showUI('lsd-upd-explain', tag);
                    } else {
                        hideUI('lsd-upd-explain', tag);
                    }
                });
            } else {
                document.querySelector('.lsd-fill-' + tag).style.display = 'none';
                hideUI('lsd-upd-explain', tag);
                hideUI('lsd-upd-access', tag);
                hideUI('lsd-upd-insurance', tag);
                hideUI('lsd-upd-yes', tag);
            }
        });
        const someone_else = document.getElementById('lsd-who-' + tag);
        someone_else.addEventListener('change', () => {
        console.log(someone_else.value);
        if (someone_else.value === 'someone-else') {
            document.querySelector('.lsd-fill-' + tag).style.display = 'table-cell';
            console.log(document.querySelector('.lsd-fill-' + tag));
        } else {
            document.querySelector('.lsd-fill-' + tag).style.display = 'none';
        }
        hideUI('check-out', tag);
        hideUI('check-in', tag);
        hideUI('psr', tag);
        hideUI('bulk-transfer', tag);
        });

        const it_equip = document.getElementById('item-type-' + tag);
        console.log(it_equip);
        it_equip.addEventListener('change', () => {
        console.log(it_equip.value);
        if (it_equip.value === 'IT Equipment') {
            console.log(it_equip.value);
            showUI('lsd-it-equip', tag);
            const confidential = document.getElementById('lsd-it-equip-confidential-' + tag);
            confidential.addEventListener('change', () => {
            if (confidential.value === 'Yes') {
                showUI('lsd-it-equip-confidential', tag);
            } else {
                hideUI('lsd-it-equip-confidential', tag);
            }
            });
            const encrypted = document.getElementById('lsd-it-equip-encrypted-' + tag);
            encrypted.addEventListener('change', () => {
            if (encrypted.value === 'Yes') {
                showUI('lsd-it-equip-encrypted', tag);
            } else {
                hideUI('lsd-it-equip-encrypted', tag);
            }
            });
        } else {
            hideUI('lsd-it-equip', tag);
            hideUI('lsd-it-equip-encrypted', tag);
            hideUI('lsd-it-equip-confidential', tag);
        }
        });
    }



    if (form_type.value === 'bulk-transfer') {
                hideUI('lsd-upd-explain', tag);
                hideUI('lsd-upd-access', tag);
            hideUI('lsd-it-equip', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd', tag);
        hideUI('lsd-upd-yes', tag);
        hideUI('lsd-upd-insurance', tag);
        hideUI('check-out', tag);
        hideUI('check-in', tag);
        hideUI('psr', tag);
        hideUI('lsd', tag);
        hideUI('lsd-fill-', tag);
            hideUI('lsd-it-equip', tag);
    }

    form_class.forEach(el => {
    el.style.display = 'table-cell';
    });
    });
    });

    const btn = document.getElementById("submit").addEventListener("click", async () => {
    let bulk_t_tags = [],
        t_tags = [],
        b_psr_tags = [],
        psr_tags = [],
        out_tags = [],
        in_tags = [],
        lsd_tags = [];
    const forms_needed = document.querySelectorAll('.forms-needed');
    const dept_id = <?= json_encode($_SESSION['info'][2]) ?>;
    const audit_id = <?= json_encode($_SESSION['info'][5]) ?>;

    const form_submitted = await fetch('https://dataworks-7b7x.onrender.com/audit/audit-history/complete/change_db.php', {
    method: 'POST',
        headers: {
        'Content-Type': 'application/json'
    },
        body: JSON.stringify({
        dept_id: dept_id,
            audit_id: audit_id
    })
    });

    if (!form_submitted.ok) {
        const text = await form_submitted.text();
        throw new Error(`HTTP ${form_submitted.status}: ${text}`);
    } else {
        const clone = form_submitted.clone();
        try {
            const data = await form_submitted.json();
            console.log("Update DB response (JSON):", data);
        } catch {
            const text = await clone.text();
            console.log("Update DB response (text):", text);
        }
    }

    forms_needed.forEach(async (type) => {
    const val = type.value;

    if (val == 'bulk-transfer') {
        bulk_t_tags.push(type.dataset.tag);
    } else if (val == 'psr') {
        const reason = document.getElementById('psr-reason-' + type.dataset.tag).value;
        const code = document.getElementById('psr-code-' + type.dataset.tag).value;
        psr_tags.push({
        tag: type.dataset.tag,
            reason: reason,
            code: code
        });
    } else if (val == 'check-out') {
        url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/check-out.php";
        const check_type = document.querySelector('.who-' + type.dataset.tag)?.value;
        let borrower = '';
        if (check_type === 'someone-else') {
            borrower = document.querySelector('.someonel-else-' + type.dataset.tag)?.value;
        } 
        const condition = document.getElementById('check-condition-' + type.dataset.tag)?.value;
        const notes = document.getElementById('check-notes-' + type.dataset.tag)?.value;
        const split_name = borrower.split(' ');

        if (check_type === 'Myself' && split_name < 2) {
            document.getElementById('check-out-borrower-msg-' + type.dataset.tag).textContent = 'Incorrect Name Format';
            exit;
        }

        const out_res = await fetch(url, {
        method: 'POST',
            headers: {
            'Content-Type': 'application/json'
        },
            body: JSON.stringify({
            form_type: 'Checking Out Equipment',
                tag: type.dataset.tag,
                type: check_type,
                borrower: borrower,
                condition: condition,
                dept_id: dept_id,
                audit_id: audit_id,
                notes: notes
        })
        });

        if (!out_res.ok) {
            const text = await out_res.text();
            throw new Error(`HTTP ${out_res.status}: ${text}`);
        } else {
            const clone = out_res.clone();
            try {
                const data = await out_res.json();
                console.log("Check out response (JSON):", data);
                hideUI('row', type.dataset.tag);
                type.value = '';
            } catch {
                const text = await clone.text();
                console.log("Check out response (text):", text);
                hideUI('row', type.dataset.tag);
                type.value = '';
            }
        }
    } else if (val == 'check-in') {
        url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/check-out.php";
        const check_type = document.querySelector('.who-' + type.dataset.tag)?.value;
        let borrower = '';
        if (check_type === 'someone-else') {
            borrower = document.querySelector('.someonel-else-' + type.dataset.tag)?.value;
        } 
        const condition = document.getElementById('check-condition-' + type.dataset.tag)?.value;
        const notes = document.getElementById('check-notes-' + type.dataset.tag)?.value;
        const split_name = borrower.split(' ');

        if (check_type === 'Myself' && split_name < 2) {
            document.getElementById('check-in-borrower-msg-' + type.dataset.tag).textContent = 'Incorrect Name Format';
            exit;
        }

        const in_res = await fetch(url, {
        method: 'POST',
            headers: {
            'Content-Type': 'application/json'
        },
            body: JSON.stringify({
            form_type: 'Returning Equipment',
                tag: type.dataset.tag,
                type: check_type,
                borrower: borrower,
                condition: condition,
                dept_id: dept_id,
                audit_id: audit_id,
                notes: notes
        })
        });

        if (!in_res.ok) {
            const text = await in_res.text();
            throw new Error(`HTTP ${in_res.status}: ${text}`);
        } else {
            const clone = in_res.clone();
            try {
                const data = await in_res.json();
                console.log("Check in response (JSON):", data);
                hideUI('row', type.dataset.tag);
                type.value = '';
            } catch {
                const text = await clone.text();
                console.log("Check in response (text):", text);
                hideUI('row', type.dataset.tag);
                type.value = '';
            }
        }
    } else if (val === 'lsd') {
        if (document_audit_id !== 4 && document_audit_id !== 5 && document_audit_id !== 6) {
            const who = document.getElementById('lsd-who-' + type.dataset.tag)?.value;
            let borrower = '';
            if (who === 'someone-else') {
                borrower = document.getElementById('lsd-fill-for-' + type.dataset.tag)?.value;
            } 
            const position = document.getElementById('lsd-position-' + type.dataset.tag).value;
            const lsd = document.getElementById('lsd-condition-' + type.dataset.tag).value;
            const reason = document.getElementById('lsd-narrative-' + type.dataset.tag).value;
            const upd = document.getElementById('upd-' + type.dataset.tag).value;
            const item_type = document.getElementById('item-type-' + type.dataset.tag).value;
            if (upd === 'Yes') {
                const date_reported = document.getElementById('upd-date-reported-' + type.dataset.tag).value;
                const time_reported = document.getElementById('upd-time-reported-' + type.dataset.tag).value;
                const date_last_seen = document.getElementById('upd-date-last-seen-' + type.dataset.tag).value;
                const time_last_seen = document.getElementById('upd-time-last-seen-' + type.dataset.tag).value;
                const by_whom = document.getElementById('upd-by-whom-' + type.dataset.tag).value;
                const upd_location = document.getElementById('upd-location-' + type.dataset.tag).value;
                const secured = document.getElementById('upd-secured-' + type.dataset.tag).value;
                const access_keys = document.getElementById('upd-access-keys-' + type.dataset.tag).value;
                const assigned_staff = document.getElementById('upd-assigned-staff-' + type.dataset.tag).value;
                const who_assigned = document.getElementById('upd-who-' + type.dataset.tag).value;
                const recovery_steps = document.getElementById('upd-recovery-steps-' + type.dataset.tag).value;
                const precautions = document.getElementById('upd-precautions-' + type.dataset.tag).value;
                const authorized = document.getElementById('upd-authorized-' + type.dataset.tag).value;
                const security = document.getElementById('upd-security-' + type.dataset.tag).value;
                const reported = document.getElementById('upd-reported-' + type.dataset.tag).value;
                const explain = document.getElementById('upd-explain-' + type.dataset.tag).value;
                const insurance = document.getElementById('upd-insurance-' + type.dataset.tag).value;
                const company = document.getElementById('upd-company-' + type.dataset.tag).value;
                const street = document.getElementById('upd-street-' + type.dataset.tag).value;
                const city = document.getElementById('upd-city-' + type.dataset.tag).value;
                const zip = document.getElementById('upd-zip-' + type.dataset.tag).value;
                const state = document.getElementById('upd-state-' + type.dataset.tag).value;
                const describe_asset = document.getElementById('upd-describe-' + type.dataset.tag).value;
                const encrypted = document.getElementById('lsd-it-equip-encrypted-' + type.dataset.tag).value;
                if (encrypted === 'Yes') {
                    const encrypted_data = document.getElementById('lsd-it-equip-encrypted-input-' + type.dataset.tag).value;
                }
                const confidential = document.getElementById('lsd-it-equip-confidential-' + type.dataset.tag).value;
                if (confidential === 'Yes') {
                    const confidential_data = document.getElementById('lsd-it-equip-confidential-input-' + type.dataset.tag).value;
                }
            }

            url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/lsd.php";
            const lsd_res = await fetch(url, {
            method: 'POST',
                headers: {
                'Content-Type': 'application/json'
            },
                body: JSON.stringify({
                    tag: type.dataset.tag,
                    who: who,
                    borrower: borrower,
                    position: position,
                    lsd: lsd,
                    reason: reason,
                    dept_id: dept_id,
                    audit_id: audit_id,
                    upd: upd,
                    item_type: item_type,
                    date_reported: date_reported,
                    time_reported: time_reported,
                    date_last_seen: date_last_seen,
                    time_last_seen: time_last_seen,
                    by_whom: by_whom,
                    upd_location: upd_location,
                    secured: secured,
                    access_keys: access_keys,
                    assigned_staff: assigned_staff,
                    who_assigned: who_assigned,
                    recovery_steps: recovery_steps,
                    precautions: precautions,
                    authorized: authorized,
                    security: security,
                    reported: reported,
                    explain: explain,
                    insurance: insurance,
                    company: company,
                    street: street,
                    city: city,
                    zip: zip,
                    state: state,
                    encrypted: encrypted,
                    describe_asset: describe_asset,
                    encrypted_data: encrypted_data,
                    confidential: confidential,
                    confidential_data: confidential_data
            })
            });
            if (!lsd_res.ok) {
                const text = await lsd_res.text();
                throw new Error(`HTTP ${lsd_res.status}: ${text}`);
            } else {
                const clone = lsd_res.clone();
                try {
                    const data = await lsd_res.json();
                    console.log("Lsd response (JSON):", data);
                    hideUI('row', type.dataset.tag);
                    type.value = '';
                } catch {
                    const text = await clone.text();
                    console.log("Lsd response (text):", text);
                    hideUI('row', type.dataset.tag);
                    type.value = '';
                }
            }
        } else {
            url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/dw-lsd.php";
            const lsd_res = await fetch(url, {
            method: 'POST',
                headers: {
                'Content-Type': 'application/json'
            },
                body: JSON.stringify({
                tag: type.dataset.tag,
                    dept_id: dept_id,
                    audit_id: audit_id
            })
            });

            if (!lsd_res.ok) {
                const text = await lsd_res.text();
                throw new Error(`HTTP ${lsd_res.status}: ${text}`);
            } else {
                const clone = lsd_res.clone();
                try {
                    const data = await lsd_res.json();
                    console.log("Lsd response (JSON):", data);
                    hideUI('row', type.dataset.tag);
                    type.value = '';
                } catch {
                    const text = await clone.text();
                    console.log("Lsd response (text):", text);
                    hideUI('row', type.dataset.tag);
                    type.value = '';
                }
            }
        }
    }


    if (psr_tags.length !== 0) {
        url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/psr.php";
        const psr_res = await fetch(url, {
        method: 'POST',
            headers: {
            'Content-Type': 'application/json'
        },
            body: JSON.stringify({
            psr_tags,
                dept_id: dept_id,
                audit_id: audit_id
        })
        });

        if (!psr_res.ok) {
            const text = await psr_res.text();
            throw new Error(`HTTP ${psr_res.status}: ${text}`);
        } else {
            const clone = psr_res.clone();
            try {
                const data = await psr_res.json();
                console.log("PSR response (JSON):", data);
                psr_tags.forEach(async (value) => {
                hideUI('row', value);
                type.value = '';
                });
            } catch {
                const text = await clone.text();
                console.log("PSR response (text):", text);
                psr_tags.forEach(async (value) => {
                hideUI('row', value);
                type.value = '';
                });
            }
        }
    }

    if (bulk_t_tags.length !== 0) {
        url = "https://dataworks-7b7x.onrender.com/kualiAPI/write/bulk-transfer.php";
        const bulk_t_res = await fetch(url, {
        method: 'POST',
            headers: {
            'Content-Type': 'application/json'
        },
            body: JSON.stringify({
            bulk_t_tags,
                dept_id: dept_id,
                audit_id: audit_id
        })
        });

        if (!bulk_t_res.ok) {
            const text = await bulk_t_res.text();
            throw new Error(`HTTP ${bulk_t_res.status}: ${text}`);
        } else {
            const clone = bulk_t_res.clone();
            try {
                const data = await bulk_t_res.json();
                console.log("bulk-transfer response (JSON):", data);
                bulk_t_tags.forEach(async (value) => {
                hideUI('row', value);
                type.value = '';
                });
            } catch {
                const text = await clone.text();
                console.log("bulk-transfer response (text):", text);
                psr_tags.forEach(async (value) => {
                hideUI('row', value);
                type.value = '';
                });
            }
        }
    }
    });
    });
});
</script>
                </body>

</html>
