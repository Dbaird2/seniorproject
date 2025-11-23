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
        if (empty($latlon['geo_x']) || empty($latlon['geo_y']) || empty($latlon['elevation'])) {
            continue;
        }
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
 <html>

 <head>
     <title>Dataworks Asset Locations</title>
     <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
         integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
         crossorigin="" />
     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
         integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
         crossorigin=""></script>
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
                 value.addEventListener('click', function()  {
                    console.log(this.dataset.x, this.dataset.y);
                     map.setView([this.dataset.x, this.dataset.y], this.dataset.ele);
                 })
             })

         }
     </script>
 </head>
 <style>
     .window {
         position: absolute;
         right: 0%;
         top: 5%;
         z-index: 9999;
         float: right;
         font-size: 1vw;
     }
     #map {
         height: 80%;
         width: 80%;
     }
 </style>

 <body>
     <h1>Reverse Geolocation</h1>
     <div id="map"></div>
     <div class="window">
         <div id="assets"><?php foreach ($results as $asset) {
                                $lat1 = $asset['geo_x'];
                                $lon1 = $asset['geo_y'];
                                $ele1 = $asset['elevation'];
                            ?>
                 <button class='btn' data-x="<?= $lat1 ?>" data-y="<?= $lon1 ?>" data-ele="<?= $ele1 ?>"><?= 'Asset: ' . $asset['asset_tag'] . ' X ' . $asset['geo_x'] . ' Y ' . $asset['geo_y'] ?></button><br>
             <?php } ?>
         </div>
     </div>
 </body>
 <script>
     function changeView(lat, lon, ele) {

         map = L.setView([lat, lon], ele);
     }
 </script>

 </html>
