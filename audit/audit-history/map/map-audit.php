 <?php
    include_once ("../../config.php");
    include_once ("../../navbar.php");

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $dept_id = $_GET['dept_id'];
    $audit_id = $_GET['audit_id'];
    $select = 'SELECT audit_history FROM audit_history WHERE dept_id = :dept AND audit_id = :audit';
    $stmt = $dbh->prepare($select);
    $stmt->execute([':dept'=>$dept_id, ':audit'=>$audit_id]);
    $results = $stmt->fetch();
    $results = json_decode($results, true);
/*
    $dbh = new PDO("sqlite:$dbFile");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $create = 'CREATE TABLE IF NOT EXISTS "asset_info" (
 "asset_tag" TEXT PRIMARY KEY,
 "geo_x" TEXT,
 "geo_y" TEXT,
 "elevation" TEXT
 )';
    $dbh->exec($create);
    $delete = 'DELETE FROM asset_info';
    $stmt = $dbh->query($delete);
    $insert = 'INSERT INTO "asset_info" ("asset_tag", "geo_x", "geo_y", "elevation") VALUES (:asset_tag, :geo_x, :geo_y, :elevation)';
    $stmt = $dbh->prepare($insert);
    $stmt->execute([
        ':asset_tag' => '23456',
        ':geo_x' => '35.7128',
        ':geo_y' => '-118.0060',
        ':elevation' => '10'
    ]);
    $stmt->execute([
        ':asset_tag' => '2356',
        ':geo_x' => '34.7128',
        ':geo_y' => '-118.0060',
        ':elevation' => '10'
    ]);
    $select = 'SELECT * FROM "asset_info"';
    $stmt = $dbh->query($select);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
 */
    $lat = [];
$lon = [];
$ele = [];
$count = $lon_total = $lat_total = 0;
foreach ($results as $latlon) {
    $count++;
    $lat[] = $latlon['geo_x'];
    $lon[] = $latlon['geo_y'];
    $ele[] = $latlon['elevation'];
    $lat_total += $latlon['geo_x'];
    $lon_total += $latlon['geo_y'];
}
$lati = $lat_total / (float)$count;
$long = $lon_total / (float) $count;

$coordinates = [];
for ($i = 0; $i < count($lat); $i++) {
    $coordinates[] = [$lat[$i], $lon[$i], $ele[$i]];
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSUB Asset Locations</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #003DA5 0%, #001a52 100%);
            min-height: 100vh;
            overflow: hidden;
        }

        /* Modern header with CSUB branding */
        .header {
            background: linear-gradient(90deg, #003DA5 0%, #004db3 100%);
            padding: 10px 30px;
            box-shadow: 0 4px 15px rgba(0, 61, 165, 0.3);
            border-bottom: 3px solid #FFB81C;
        }

        .header h1 {
            color: #FFB81C;
            font-size: 2em;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95em;
            margin-top: 5px;
        }

        .container {
            display: flex;
            height: calc(100vh - 80px);
            gap: 20px;
            padding: 20px;
        }

        /* Modern map container with rounded corners and shadow */
        #map {
            flex: 1;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            border: 3px solid #FFB81C;
            overflow: hidden;
        }

        /* Redesigned asset panel */
        .asset-panel {
            width: 320px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .asset-panel-header {
            background: linear-gradient(90deg, #003DA5 0%, #004db3 100%);
            padding: 20px;
            border-bottom: 3px solid #FFB81C;
        }

        .asset-panel-header h2 {
            color: #FFB81C;
            font-size: 1.3em;
            font-weight: 700;
        }

        .asset-panel-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.85em;
            margin-top: 5px;
        }

        #assets {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }

        /* Modern button styling with CSUB colors and hover effects */
        .btn {
            display: block;
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #003DA5 0%, #004db3 100%);
            color: #FFB81C;
            border: 2px solid #FFB81C;
            border-radius: 8px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
            box-shadow: 0 4px 12px rgba(0, 61, 165, 0.2);
        }

        .btn:hover {
            background: linear-gradient(135deg, #FFB81C 0%, #ffc837 100%);
            color: #003DA5;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 184, 28, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Scrollbar styling */
        #assets::-webkit-scrollbar {
            width: 8px;
        }

        #assets::-webkit-scrollbar-track {
            background: #f0f0f0;
        }

        #assets::-webkit-scrollbar-thumb {
            background: #003DA5;
            border-radius: 4px;
        }

        #assets::-webkit-scrollbar-thumb:hover {
            background: #FFB81C;
        }

        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }

            .asset-panel {
                width: 100%;
                height: 250px;
            }

            #map {
                height: 100%;
            }
        }
    </style>
    <script>
        var map;
        window.onload = function() {
            var lat = <?= json_encode($lati) ?>;
            var lon = <?= json_encode($long) ?>;
            var ele = <?= json_encode($ele[0]) ?>;
            map = L.map('map').setView([lat, lon], ele - 2);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            let coordinates = <?php echo json_encode($coordinates); ?>;
            coordinates.forEach(function(coord) {
                let lat = coord[0];
                let lon = coord[1];
                let ele = coord[2];
                let lat2 = parseFloat(lat);
                let lon2 = parseFloat(lon);
                let ele2 = parseFloat(ele);
                var marker = L.marker([lat2, lon2]).addTo(map);
            });

            const btns = document.querySelectorAll('.btn');
            btns.forEach((value) => {
                value.addEventListener('click', function() {
                    console.log(this.dataset.x, this.dataset.y);
                    map.setView([this.dataset.x, this.dataset.y], this.dataset.ele);
                })
            })
        }
    </script>
</head>

<body>
    <div class="header">
        <h1>🗺️ Audit <?= $dept_id ?> Geolocation</h1>
        <p>CSUB Distribution Services</p>
    </div>

    <!-- Reorganized layout with flex container -->
    <div class="container">
        <div id="map"></div>

        <!-- Modern asset panel with improved styling -->
        <div class="asset-panel">
            <div class="asset-panel-header">
                <h2>Assets</h2>
                <p><?= count($results) ?> locations</p>
            </div>
            <div id="assets">
                <?php foreach ($results as $asset) {
                    $lat1 = $asset['geo_x'];
                    $lon1 = $asset['geo_y'];
                    $ele1 = $asset['elevation'];
                ?>
                    <button class='btn' data-x="<?= $lat1 ?>" data-y="<?= $lon1 ?>" data-ele="<?= $ele1 ?>">
                        <strong><?= $asset['asset_tag'] ?? $asset['Tag Number'] . ' ' . $asset['asset_name'] ?? '' ?></strong><br>
                        <span style="font-size: 0.85em; opacity: 0.9;">X: <?= $lat1 ?> | Y: <?= $lon1 ?></span>
                    </button>
                <?php } ?>
            </div>
        </div>
    </div>
</body>

</html>
