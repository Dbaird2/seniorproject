<?php 
include_once("../../config.php");
check_auth();

include_once("../../navbar.php");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Audit Search</title>
    <!-- Including jQuery is required. -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
    integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
    crossorigin="anonymous"></script>
    <script type="text/javascript" src="script.js"></script>
    <link rel="stylesheet" href="audit-history.css">

</head>
<body>
    
<!-- Search box. -->
<div class="asset-search">
    <div class="container">
        <h2>Audit History</h2>
    </div>
    <div class="input-seciton">
    <input class = "search-input" type="hidden" name="offset" id="offset">
    <input class="search-input" type="text" name="search" id="search" placeholder="Search for an audit..." > 
    <select class="search-input" name="audit-type" id="audit-type">
        <option value="Self Audits">Self Audits</option>
        <option value="Management Audits">Management Audits</option>
        <option value="SPA Audits">SPA Audits</option>
    </select>
    <button id="button-9">Search</button>
    </div>
  <br>
  <br />
  <!-- Suggestions will be displayed in below div. -->
    <div id="display">
        <table class="is-history" id="is-history">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Auditor</th>
                    <th>Audit ID</th>
                </tr>
            </thead>
            <tbody>
           <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
           </tr>
            </tbody>
        </table>
    </div>
  </div>
<script>
 document.addEventListener('DOMContentLoaded', () => {
        console.log('hello');
        const modal_btn = document.querySelectorAll('.modal-btn');
        const span = document.querySelectorAll('.close');
        console.log(modal_btn, span);
        modal_btn.forEach(function(btn) {
            console.log(btn);
            btn.addEventListener('click', () => {
                const dept = btn.dataset.dept;
                const index = btn.dataset.id;
                console.log(dept, index);
                document.getElementById('form-modal-' + dept + '-' + index).style.display = 'block';
                span.forEach(function(btn) {
                    btn.addEventListener('click', () => {
                        document.getElementById('form-modal-' + dept + '-' + index).style.display = 'none';
                    });
                });
                window.onclick = function(event) {
                    if (event.target == document.getElementById('form-modal-' + dept + '-' + index)) {
                        document.getElementById('form-modal-' + dept + '-' + index).style.display = "none";
                    }
                }
            });

        });

    });

</script>
</body>
</html>
