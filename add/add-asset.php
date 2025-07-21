<?php include_once ("../config.php");
check_auth('high');
?>
<!DOCTYPE html>
<html lang="en">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>add-asset.php</title>
        <style>
            body {
                margin:0;
            }
.has_asset {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    min-height: 100vh;
    width:100%;
}
.container {
    width:100%;
    display: flex;
    flex-direction:inline-block;
    color:lightblue;
    box-shadow: 4px 4px 8px rgba(0,0,0,0.05);
    max-height:80vh;
    overflow-y:auto;
    justify-content:center;
}
.middle {
    display:flex; 
    flex-direction:column;
    justify-content:center;
}
.container button {
    max-width:15%;
    -ms-transform: translateX(-50%);
    transform: translateX(200%);
 }
.container > div {


}
.container input {

}
.container label {
    padding: 5px 5px;
    color: black;
}
.form {
    display:flex;
    justify-content:center;

}
.form-header {
    background: linear-gradient(180deg, #1976d2 0%, #2196f3 100%);
    color: white;
    text-align: center;
    padding: 30px 20px;
    position: relative;
    border-radius: 8px;
    width:20%;
}
.bigger-input {
    height:5vh;
    resize: vertical;
    padding: 10px;
}
@media (max-width: 480px) {
    .sub-body {
        margin: 10px;
        max-width: none;
    }

    .login-body {
        padding: 30px 20px 20px;
    }

    .form-header {
        padding: 25px 20px;
    }

    .form-header h1 {
        font-size: 24px;
    }
}
.input-container {
  padding: 5px 10px;
}

.input {
  font-size: 1em;
  padding: 0.6em 1em;
  border: none;
  border-radius: 6px;
  background-color: #f8f8f8;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
  max-width: 100%;
  color: #333;
}

.input:hover {
  background-color: #f2f2f2;
}

.input:focus {
  outline: none;
  background-color: #fff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.input::placeholder {
  color: #999;
}

.highlight {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background-color: #6c63ff;
  transition: width 0.3s ease;
}

.input:focus + .highlight {
  width: 100%;
}

/* Optional: Animation on focus */
@keyframes input-focus {
  from {
    transform: scale(1);
    box-shadow: 0 0 0 rgba(0, 0, 0, 0.1);
  }

  to {
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
}

.input:focus {
  animation: input-focus 0.3s ease;
}

</style>
<body>
            <?php include_once ("../navbar.php"); ?>

    <div class="has_asset">
                <div class="form">
                            <h1 class='form-header'>Asset Addition Form</h1>
                                    </div>
                                        <div class="container">
                                                <div class="middle">
                                                        <div class="input-container">
                                                                    <label for="po">Asset Tag</label>    
                                                                                <input type="text" name="tag" class="input" placeholder="Ex: 12345">
                                                                                            <div class="highlight"></div>
                                                                                                    </div>
                                                                                                            <div class="input-container">
                                                                                                                        <label for="po">Asset Description</label>    
                                                                                                                                    <input type="text" name="descr" class="input" placeholder="Asset DescriptionEx: DELL LATITUDE 5450">
                                                                                                                                                <div class="highlight"></div>
                                                                                                                                                        </div>
                                                                                                                                                                <div class="input-container">
                                                                                                                                                                            <label for="po">Asset Type</label>    
                                                                                                                                                                                        <input type="text" name="type" class="input" placeholder="Ex: Equipment">
                                                                                                                                                                                                    <div class="highlight"></div>
                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                    <div class="input-container">
                                                                                                                                                                                                                                <label for="po">Acquisiton Date: </label>    
                                                                                                                                                                                                                                            <input type="date" name="acq" class="input" >
                                                                                                                                                                                                                                                        <div class="highlight"></div>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                        <div class="input-container">
                                                                                                                                                                                                                                                                                    <label for="po">Serial Number</label>    
                                                                                                                                                                                                                                                                                                <input type="text" name="po" class="input" placeholder="Ex: 7BXS36">
                                                                                                                                                                                                                                                                                                            <div class="highlight"></div>
                                                                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                                                                            <div class="input-container">
                                                                                                                                                                                                                                                                                                                                        <label for="po">Asset Model</label>    
                                                                                                                                                                                                                                                                                                                                                    <input type="text" name="po" class="input" placeholder="Ex: DELL">
                                                                                                                                                                                                                                                                                                                                                                <div class="highlight"></div>
                                                                                                                                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                                                                                                                                                <div class="input-container">
                                                                                                                                                                                                                                                                                                                                                                                            <label for="po">Total Cost</label>    
                                                                                                                                                                                                                                                                                                                                                                                                        <input type="text" name="po" class="input" placeholder="Ex: 550">
                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="highlight"></div>
                                                                                                                                                                                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                                                                                                                                                                                                    <div class="input-container">
                                                                                                                                                                                                                                                                                                                                                                                                                                                <label for="email" class="">Department ID</label>
                                                                                                                                                                                                                                                                                                                                                                                                                                                <select class="input" placeholder="Select Department ID" id="dept-id" class="">
          <?php foreach ($dept_ids as $index=>$row) {  ?>

                              <option  class=""><?=$row?></option>
                                         <?php } ?>
                                                 </select>
                                                         </div>
                                                                 <div class="input-container">
                                                                             <label for="po">Building Name</label>    
                                                                                         <input type="text" name="po" class="input" placeholder="Building Name">
                                                                                                     <div class="highlight"></div>
                                                                                                             </div>
                                                                                                                     <div class="input-container">
                                                                                                                                 <label for="po">Room Name/Number</label>    
                                                                                                                                             <input type="text" name="po" class="input" placeholder="Room Name/Number">
                                                                                                                                                         <div class="highlight"></div>
                                                                                                                                                                 </div>
                                                                                                                                                                         <div class="input-container">
                                                                                                                                                                                     <label for="po">Profile ID</label>    
                                                                                                                                                                                                 <input type="text" name="po" class="input" placeholder="Profile ID">
                                                                                                                                                                                                             <div class="highlight"></div>
                                                                                                                                                                                                                     </div>
                                                                                                                                                                                                                             <div class="input-container">
                                                                                                                                                                                                                                         <label for="po">Purchase Order</label>    
                                                                                                                                                                                                                                                     <input type="text" name="po" class="input" placeholder="Purchase Order">
                                                                                                                                                                                                                                                                 <div class="highlight"></div>
                                                                                                                                                                                                                                                                         </div>
                                                                                                                                                                                                                                                                                 <div class="input-container">
                                                                                                                                                                                                                                                                                             <label for="tag">Notes</label>    
                                                                                                                                                                                                                                                                                                         <textarea class="input bigger-input"type="text" id="tag" name="tag" placeholder="Ex: 12345"></textarea>
                                                                                                                                                                                                                                                                                                                 </div>
                                                                                                                                                                                                                                                                                                                         <button>Submit</button>
                                                                                                                                                                                                                                                                                                                             </div>
                                                   </div>
    </div>
</body>
</html>

