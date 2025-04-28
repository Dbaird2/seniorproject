<!DOCTYPE html>
<html>

<head>
    <style>
        .excel-info {
            border: 2px outset black;
            background-color: white;
            text-align: left;
            width: 60%;
            margin-bottom: -0.0em;
            display: inline-block;
        }

        li {
            list-style-type: none;
        }

        .inner-text {
            margin-top: -1vh;
            margin-bottom: -1vh;
            font-size: 0.8em;
        }

        .show-tags {
            position: absolute;
            top: 0;
            right: 34vh;
        }

        .show-tags li {
            padding-top: 1em;
            margin-top: -5%;
            font-size: 80%;
            margin-bottom: -2.5vh;
        }

        body {
            margin: 0;
            height: 100vh;
        }

        #dynamicForm {
            position: absolute;
            top: 1vh;
            right: 10.5vh;
            padding: 10px;
            background-color: white;
            border: 1px solid white;
        }
    </style>
    <title>Asset Management Excel</title>
</head>

<body>
    <form id="sheet" name="form" action="index.php" method="POST" enctype="multipart/form-data">
        <label for="filePath"> Enter File: </label>
        <input type="file" name="file" id="filePath">
        <br>
        <button type="submit">Submit</button>
    </form>

    <?php
    require __DIR__ . '/vendor/autoload.php';

    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Style\Alignment;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;

    // Handle file upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $filePath = handleFileUpload();
    }

    // Handle form to create new sheet
    if (isset($_POST['create'])) {
        createExcelSheet();
    }

    // Load spreadsheet and display content
    $worksheet = loadSpreadsheet($filePath);
    if ($worksheet) {
        displaySpreadsheetContent($worksheet);
    }

    // Function to handle file upload
    function handleFileUpload() {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }
        $targetFilePath = $uploadDir . basename($fileName);

        /*
        $allowedFileTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($fileType, $allowedFileTypes)) {
            echo "Invalid file type.";
            exit;
        }
         */

        if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
            echo "File uploaded successfully to $targetFilePath";
        } else {
            echo "Error uploading file.";
        }
        return $targetFilePath;
    }

    // Function to create Excel sheet
    function createExcelSheet() {
        try {
            $saveDir = __DIR__ . '/exports/';
            if (!file_exists($saveDir)) {
                mkdir($saveDir, 0777, true);
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $column_letters = ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1'];

            $headers = $_POST['headers'];
            $loc = $_POST['loc'];
            $sn = $_POST['serial'];
            $po = $_POST['po_num'];
            $old_tags = $_POST['old_tag'];
            $desc = $_POST['description'];

            // Construct file path and save
            $filePath = constructFilePath($saveDir);
            $sheet = populateSheet($sheet, $column_letters, $headers, $old_tags, $desc, $loc, $sn, $po);

            // Save and redirect
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            header('Location: download.php?file=' . urlencode($filePath));
        } catch (Exception $e) {
            echo "Something went wrong: " . $e->getMessage();
        }
    }

    // Function to construct file path
    function constructFilePath($saveDir) {
        $fileNameOnly = basename($_FILES['file']['name']);
        $filePath = str_replace([".xlsx", ".xls"], "_AUDIT.xlsx", $saveDir . $fileNameOnly);
        return $filePath;
    }

    // Function to populate sheet
    function populateSheet($sheet, $column_letters, $headers, $old_tags, $desc, $loc, $sn, $po) {
        $row_index = 2;
        for ($i = 0; $i < count($column_letters); $i++) {
            $sheet->setCellValue($column_letters[$i], $headers[$i]);
        }

        $sheet->setCellValue('I1', 'Extra Tags');
        foreach ($old_tags as $index => $tag) {
            $sheet->setCellValue('A' . $row_index, $tag);
            $sheet->setCellValue('D' . $row_index, $desc[$index]);
            $sheet->setCellValue('F' . $row_index, $loc[$index]);
            $sheet->setCellValue('G' . $row_index, $po[$index]);
            $row_index++;
        }
        return $sheet;
    }

    // Function to load the spreadsheet
    function loadSpreadsheet($filePath) {
        try {
            $spreadsheet = IOFactory::load($filePath);
            return $spreadsheet->getActiveSheet();
        } catch (Exception $e) {
            echo "Error loading file: " . $e->getMessage();
            return null;
        }
    }

    // Function to display spreadsheet content
    function displaySpreadsheetContent($worksheet) {
        $row_number = 1;
        $tag_array = [];
        $colors = ['lightgray', 'white'];

        foreach ($worksheet->getRowIterator() as $row) {
            $color = $colors[$row_number % 2];
            $cellB = $worksheet->getCell('B' . $row->getRowIndex());
            $cellH = $worksheet->getCell('H' . $row->getRowIndex());

            $tag = $cellB->getValue();
            $tag_array[] = $tag;

            echo "<div class='excel-info' style='border-style: solid; margin-bottom: 1em;'>";
            echo "<div style='background-color: $color;' class='inner-text'>";
            echo "<ul>";
            echo "<li><b>" . $row_number . "</b></li>";
            echo "<li><b>Description:</b> " . $cellH->getValue() . "</li>";
            echo "</ul>";
            echo "</div>";
            echo "</div>";
            $row_number++;
        }

        echo "<div class='show-tags'>";
        echo "<h3>Tags Scanned</h3>";
        foreach ($tag_array as $tag) {
            echo "<b><li>$tag</li></b>";
        }
        echo "</div>";
    }
    ?>

    <!-- Additional form and dynamic input handling -->
    <div id="additionalInputs"></div>
    <form id="dynamicForm" method="POST" action="index.php" onLoad="addNewInput()" enctype="multipart/form-data">
        <label for="inputContainer"> Enter Tags: </label>
        <div id="inputContainer">
            <input type="text" name="dynamicInput[]" placeholder="Enter Tag" onfocus="addNewInput()">
        </div>

        <button type="button" id="addInputButton" onClick="addNewInput()">Add Field</button>
        <button type="submit" id="dynamicSubmit" onSubmit="doNotReload()">Submit</button>
    </form>

    <form id="makeSheet" method="POST" action="index.php" enctype="multipart/form-data">
        <button type="submit" id="create" name="create">Export Excel File</button>
    </form>

    <script>
        function addNewInput() {
            const inputDiv = document.createElement('div');
            inputDiv.classList.add('input-container');

            const newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.name = 'dynamicInput[]';
            newInput.placeholder = 'Enter tag';
            newInput.classList.add('dynamic-input');

            newInput.addEventListener("focus", addNewInput, false);

            const timeInput = document.createElement('input');
            timeInput.type = 'hidden';
            timeInput.name = 'dynamicTime[]';
            timeInput.value = getFormattedDateTime();

            inputDiv.appendChild(timeInput);
            inputDiv.appendChild(newInput);

            document.getElementById('inputContainer').appendChild(inputDiv);
        }

        function getFormattedDateTime() {
            const currentDate = new Date();
            let month = currentDate.getMonth() + 1;
            let day = currentDate.getDate();
            let year = currentDate.getFullYear();
            month = month.toString().padStart(2, '0');
            day = day.toString().padStart(2, '0');
            let formattedDate = `${month}:${day}:${year}`;
            let formattedTime = currentDate.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            return `${formattedDate} ${formattedTime}`;
        }

        function doNotReload(event) {
            event.preventDefault();
        }
    </script>
</body>

</html>
