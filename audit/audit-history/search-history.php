<?php
include_once("../../config.php");
check_auth();

include_once("../../navbar.php");
include_once ("../../ui/toast.php");
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
    <!-- Added inline styles for CSUB theme -->
    <style>
        .asset-search {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 0px;
            margin: 20px auto;
            max-width: 1600px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .container h2 {
            color: #003DA5;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .input-seciton {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .search-input {
            padding: 14px 16px;
            border: 2px solid #003DA5;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: white;
            color: #333;
            box-shadow: 0 2px 4px rgba(0, 61, 165, 0.1);
        }
        
        .search-input:focus {
            outline: none;
            border-color: #FFB81C;
            box-shadow: 0 0 0 3px rgba(255, 184, 28, 0.1);
        }
        
        #button-9 {
            padding: 14px 28px;
            background: linear-gradient(135deg, #003DA5, #002870);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(0, 61, 165, 0.3);
        }
        
        #button-9:hover {
            background: linear-gradient(135deg, #FFB81C, #E5A319);
            color: #003DA5;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 184, 28, 0.4);
        }
        
        .is-history {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .is-history thead {
            background: linear-gradient(135deg, #003DA5, #002870);
        }
        
        .is-history thead th {
            padding: 20px;
            color: white;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .is-history tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .is-history tbody tr:hover {
            background: linear-gradient(135deg, rgba(0, 61, 165, 0.05), rgba(255, 184, 28, 0.05));
        }
        
        .is-history tbody th {
            padding: 15px;
            color: #2c3e50;
            font-weight: 500;
        }
    </style>
</head>
<body>

<!-- Search box. -->
<div class="asset-search">
    <div class="container">
<?php if (isset($_GET['reason'])) { 
$reason = ($_GET['reason'] === 'in-progress') ? 'Forms are still in progress' : 'Audit form complete'; 
echo "<script>showToast(".json_encode($reason).", 5000)</script>";
 } ?>
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
async function openModal(dept, index) {
    document.body.style.overflow = 'hidden';
    document.getElementById('form-modal-' + dept + '-' + index).style.display = 'block';
}
async function closeModal(dept, index) {
    document.getElementById('form-modal-' + dept + '-' + index).style.display = 'none';
}
function openDialog(dept, index) {
    const scroll_y = window.scrollY;
    document.getElementById(dept + '-' +index).showModal();
    requestAnimationFrame(() => {
        window.scrollTo(0, scroll_y);
    });
}
function closeDialog(dept, index) {
    document.getElementById(dept + '-' +index).close();
}

window.onclick = function(event) {
    console.log(event.target);
    const modal = event.target;
    if (modal.classList.contains('modal')) {
        modal.style.display = 'none';
    }
}
</script>
</body>
</html>
