<?php
include_once("../config.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['role']) {
    header("location: https://7b7x-dataworks.onrender.com/auth/login.php");
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    header("location: https://7b7x-dataworks.onrender.com/index.php");
    exit;
}
include_once("../navbar.php");
$success = [];

$room_select = "SELECT * FROM room_table AS r NATURAL JOIN bldg_table AS b";
$room_stmt = $dbh->prepare($room_select);
$room_stmt->execute();
$result = $room_stmt->fetchAll(PDO::FETCH_ASSOC);

$dept_select = "SELECT dept_id FROM dept_table";
$dept_stmt = $dbh->prepare($dept_select);
$dept_stmt->execute();
$dept_ids = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);

$unique_bldg_names = [];
foreach($result as $row) {
    if (!in_array($row['bldg_name'], $unique_bldg_names)) {
        $unique_bldg_names[] = $row['bldg_name'];
    }
}
?>
<style>
    body {
         font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        min-height: 100vh;
        position:relative;
        top:8vh;
    }
    </style>
  <script src="https://cdn.tailwindcss.com"></script>

  <div class=" border border-blue-200 rounded-xl shadow-sm p-6 ">
    <div class="flex justify-center rounded-xl ">
  <p class="antialiased text-base text-inherit font-bold mb-1 ">Asset Addition Form</p>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 mt-8 gap-6">

    <div class="space-y-1">
      <label for="tag" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Asset Tag</label>
      <input id="tag" type="text" placeholder="Ex: 12345" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer" />
    </div>

    <div class="space-y-1">
      <label for="descr" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Asset Description</label>
      <input id="descr" type="text" placeholder="Ex: DELL LATITUDE 5450" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer" />
    </div>

    <div class="space-y-1">
      <label for="type" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Asset Type</label>
        <input id="type" aria-expanded="false" placeholder="Ex: Equipment" type="text" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer" />
    </div>

    <div class="space-y-1">
      <label for="acq-date" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Acquisition Date</label>
      <input id="acq-date" type="date" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer appearance-none" />
    </div>

    <div class="space-y-1">
      <label for="sn" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Serial Number</label>
        <input id="sn"  placeholder="Ex: 7B7XHJ3" type="text" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer" />
    </div>

    <div class="space-y-1">
      <label for="model" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Asset Model</label>
        <input id="model"  placeholder="Ex: DELL" type="text" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer" />
    </div>

    <div class="space-y-1">
      <label for="cost" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Total Cost</label>
        <input id="cost" aria-expanded="false" placeholder="Ex: 1060.50" type="text" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer" />
    </div>

    <div class="space-y-1">
      <label for="email" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Department ID</label>
<select data-dui-toggle="dropdown" aria-expanded="false" placeholder="Select Department ID" type="text"  id="dept-id" class="w-full mt-1 aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer">
          <?php foreach ($dept_ids as $index=>$row) { ?>
                    <option  class="option block px-4 py-2 text-sm text-blue-800 hover:bg-blue-100 rounded-md"><?=$row['dept_id']?></option>
           <?php } ?>
        </select>
        </div>

        <div class="space-y-1">
          <label for="bldg-name" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Building Name</label>
    <select data-dui-toggle="dropdown" aria-expanded="false" id="bldg-name" placeholder="Select Building" type="text"  class="w-full mt-1 aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer">
              <?php foreach ($unique_bldg_names as $index=>$row) { ?>
                        <option  class="option block px-4 py-2 text-sm text-blue-800 hover:bg-blue-100 rounded-md"><?=$row?></option>
               <?php } ?>
            </select>
            </div>

    <div class="space-y-1">
      <label for="room-names" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Room Name/Number</label>
<select data-dui-toggle="dropdown" aria-expanded="false" id="room-names" placeholder="Select Gender" type="text"  class="w-full mt-1 aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer">

        </select>
        </div>


    <div class="space-y-1">
              <label for="profile" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Profile ID</label>

<select data-dui-toggle="dropdown" id="profile" aria-expanded="false" placeholder="Select Profile" type="text"  class="w-full mt-1 aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer">
          <option  class="option block px-4 py-2 text-sm text-blue-800 hover:bg-blue-100 rounded-md">EQUIP-05</option>
          <option  class="option block px-4 py-2 text-sm text-blue-800 hover:bg-blue-100 rounded-md">EQUIP-10</option>
        </select>  
</div>
<div class="space-y-1">
      <label for="po" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Purchase Order</label>
        <input id="po" aria-expanded="false" placeholder="Ex: 1001023403" type="text" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer" />
    </div>

    <div class="space-y-1">
      <label for="notes" class="block mb-1 text-sm font-semibold antialiased text-blue-800">Notes</label>
            <textarea name="about" id="notes" rows="3" class="w-full aria-disabled:cursor-not-allowed outline-none focus:outline-none text-blue-800 dark:text-black placeholder:text-blue-600/60 ring-transparent border border-blue-200 transition-all ease-in disabled:opacity-50 disabled:pointer-events-none select-none text-sm py-2 px-2.5 ring shadow-sm bg-white rounded-lg duration-100 hover:border-blue-300 hover:ring-none focus:border-blue-400 focus:ring-none peer"></textarea>
            <p class="mt-3 text-sm/6 text-gray-600">Extra notes for the asset.</p>
    </div>



  </div>
  <div class="item flex flex-wrap justify-center gap-4">
    <button id="submit" onclick="submitPage()" class="inline-flex items-center justify-center border align-middle select-none font-sans font-medium text-center duration-300 ease-in disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed focus:shadow-none text-sm py-2 px-4 shadow-sm hover:shadow-md bg-stone-800 hover:bg-blue-900 relative bg-opacity-0 border-blue-500 text-black rounded-lg hover:border-blue-900 after:absolute after:inset-0 after:rounded-[inherit] after:box-shadow after:shadow-[inset_0_1px_0px_rgba(255,255,255,0.25),inset_0_-2px_0px_rgba(0,0,0,0.35)] after:pointer-events-none transition-all antialiased">Add Asset</button>
    </div>
</div>


<p class="font-sans text-md text-blue-500 mt-2 text-center">
For mass/bulk asset adding <a href="https://www.7b7x-dataworks.onrender.com/add-assets/bulk_add.php" target="_blank" class="underline text-blue-800 hover:text-blue-900">
    Click Here</a>.
</p>
<script>

result = <?= json_encode($result)?>;

function submitPage() {
    const profile = document.getElementById('profile').value;
    const room_name = document.getElementById('room-names').value;
    const bldg_name = document.getElementById('bldg-name').value;
    const tag = document.getElementById('tag').value;
    const descr = document.getElementById('descr').value;
    const sn  = document.getElementById('sn').value;
    const po = document.getElementById('po').value;
    const model = document.getElementById('model').value;
    const dept_id = document.getElementById('dept-id').value;
    const acq_date = document.getElementById('acq-date').value;
    const type = document.getElementById('type').value;
    const notes = document.getElementById('notes').value;

    const url = "insert-asset.php";

    try {
        fetch(url, {
        method: "POST",
            body: JSON.stringify ({
            tag: tag,
                descr: descr,
                sn: sn,
                po: po,
                model: model,
                acq_date: acq_date,
                profile: profile,
                type: type,
                dept_id: dept_id,
                room_name: room_name,
                bldg_name: bldg_name,
                notes: notes
    }),
        headers: {
        "Content-type": "application/json; charset=UTF-8"
    }
    })
        .then((response) =>response.json)
        .then((json) =>console.log(json));
    } catch (error) {
        console.log(error);
    }

}

document.addEventListener("DOMContentLoaded", () => { 
const bldg_dropdown = document.getElementById("bldg-name");

const room_names = document.getElementById("room-names");
bldg_dropdown.addEventListener("change", (e)=> {
room_names.options.length = 0;
result.forEach((input,index)=>{
if (input['bldg_name'] == bldg_dropdown.value) {
    const new_option = document.createElement('option');
    new_option.value = input['room_loc'];
    new_option.textContent = input['room_loc'];
    room_names.appendChild(new_option);
}
});
})
    result.forEach((input,index)=>{
    if (input['bldg_name'] == bldg_dropdown.value) {
        const new_option = document.createElement('option');
        new_option.value = input['room_loc'];
        new_option.textContent = input['room_loc'];
        room_names.appendChild(new_option);
    }
});
})

    </script>
