<?php
include_once __DIR__ . '/../config.php';
set_time_limit(900);

file_put_contents(__DIR__ . '/debug.log', date('c') . " FILE LOADED\n", FILE_APPEND);

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr');
error_reporting(E_ALL);

// ---------------------------------------------------------------------------
// Kuali field key constants
// ---------------------------------------------------------------------------
const KUALI_DEPT_ID   = 'IOw4-l7NsM';
const KUALI_DEPT_NAME = 'AkMeIWWhoj';

// ---------------------------------------------------------------------------
// Kuali app IDs
// ---------------------------------------------------------------------------
const APP_CUST_RESPONSIBILITY = '67bf42240472a7027dd17e97';
const APP_ASSET_ADDITION      = '67ec557474c52c027eca23d8';
const APP_ASSET_RECEIVED      = '67b8c49871c3d6028236d586';
const APP_BULK_PSR            = '67c9d5af2017390283de33d5';
const APP_BULK_TRANSFER       = '686554f17ba08e02806b14b5';
const APP_CHECK_OUT           = '677d53d969ef4601572b80ae';
const APP_LSD                 = '677c075baba4e3014ca39095';
const APP_PSR                 = '68093820dec1b8027f980167';
const APP_PROPERTY_TRANSFER   = '67e451d2cc3194027dfce429';
const APP_SPA_BUS_CHANGE      = '691df89db23137028e39230a';
const APP_DW_BULK_TRANSFER    = '68c73600df46a3027d2bd386';
const APP_DW_CHECK_OUT        = '68bf09aaadec5e027fe35187';
const APP_DW_LSD              = '68d09e41d599f1028a9b9457';
const APP_DW_PSR              = '68d09dcd7688dc028af9b5e7';
const APP_DW_LSD_V2           = '68e94e8a58fd2e028d5ec88f';
const APP_AUDIT_SCHEDULE      = '682622ce355ca4027e35d52a';
const APP_COMPLETE_AUDIT      = '67e450e3cc3194027d15a8e2';
const APP_DW_COMPLETE_AUDIT   = '68e5ccf75911b5028c9e9d3e';

// ---------------------------------------------------------------------------
// Property transfer form type IDs
// ---------------------------------------------------------------------------
const TRANSFER_TYPE_DEPT = 'S5VuLLJDQ1c';
const TRANSFER_TYPE_BUS  = 'HR0TQfdrMn2';

// ---------------------------------------------------------------------------
// Asset lifecycle profiles
// ---------------------------------------------------------------------------
const ASSET_PROFILE_MAP = [
    'EQUIP-10'   => 10,
    'NONCAPCOMP' => 10,
    'EQUIP-20'   => 20,
    'EQUIP-05'   => 5,
    'EQUIPAUTO'  => 20,
    'OTHIMP-10'  => 10,
    'OTHIMP-20'  => 20,
    'OTHIMP-30'  => 30,
    'OINTN'      => 10,
    'NONCAPOTHR' => 10,
    'NONCAPAUTO' => 20,
    'EQUIPCOMP'  => 10,
];

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
$result = $query_repo->fetchOne("SELECT * FROM kuali_table");

deleteOverdueSchedule();
propertyTransfer();
addKualiInfo();
assetAddition();
assetReceived();
bulkPsr();
bulkTransfer();
checkOut();
lsd();
psr();
dwBulkTransfer();
dwCheckOut();
dwLsd();
dwPsr();
checkFormStatus();
getAuditSchedules();
// completeAudit();
dwCompleteAudit();
dwLsdV2();
// spaBusChange();


// ===========================================================================
// Shared helpers
// ===========================================================================

/**
 * Returns whether a tag number matches any known asset tag format.
 */
function isValidTagFormat(string $tag): bool
{
    $patterns = [
        "/^A[SI]?\d+$/",   // ASI tags
        "/^S[RC]?[TU]?\d+$/", // STU tags
        "/^\d+/",           // CMP tags
        "/^F[DN]?\d+$/",   // FDN tags
        "/^SP\d+$/",        // SPA tags
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $tag)) return true;
    }
    return false;
}

function isItEquipment(string $name): int
{
    $pattern = '/\b(LENOVO|APPLE|DELL|HP|CPU|MACBOOK|CHROMEBOOK|TABLET|SERVER|PRECISION\s\d*\sTOWER|iPAD)\b/i';
    return preg_match($pattern, $name) ? 1 : 0;
}

/**
 * Returns true if a tag value is considered empty/invalid.
 */
function isEmptyTag(?string $tag): bool
{
    return $tag === null || $tag === '' || $tag === 'N/A' || $tag === 'NA';
}

/**
 * Generates a random temporary password.
 */
function generateTempPassword(): string
{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $password = [];
    for ($i = 0; $i < 8; $i++) {
        $password[] = $alphabet[rand(0, strlen($alphabet) - 1)];
    }
    $password[] = '-123A';
    return implode($password);
}

/**
 * Parses a Kuali displayName string ("First Last (email@domain.com)")
 * and returns its components.
 */
function parseDisplayName(string $displayName): array
{
    $parts     = explode(' ', $displayName);
    $count     = count($parts);
    $email     = trim($parts[$count - 1], '()');
    $firstName = $parts[0];
    $lastName  = implode(' ', array_slice($parts, 1, $count - 2));
    $fullName  = trim(implode(' ', array_slice($parts, 0, $count - 1)));
    $username  = explode('@', $email)[0];

    return compact('email', 'firstName', 'lastName', 'fullName', 'username');
}

/**
 * Resolves the signature value from a Kuali signature node.
 * Uses the signed name if type is 'type', otherwise falls back to the full name.
 */
function resolveSignature(array $signatureNode, string $fallbackName): string
{
    if (($signatureNode['signatureType'] ?? '') === 'type') {
        return $signatureNode['signedName'];
    }
    return $fallbackName;
}

/**
 * Marks an asset as Disposed if it is currently In Service.
 */
function disposeAsset(string $tag): void
{
    global $query_repo;
    $inService = $query_repo->fetchOne(
        "SELECT 1 FROM asset_info WHERE asset_tag = ? AND asset_status = 'In Service'",
        $tag
    );
    if ($inService) {
        $query_repo->execute("UPDATE asset_info SET asset_status = 'Disposed' WHERE asset_tag = ?", $tag);
    }
}

/**
 * Ensures a building exists in the database and that its ID and name are consistent.
 * Inserts the building if missing.
 */
function upsertBuilding(int $bldgId, string $bldgName): void
{
    global $query_repo;

    $existing = $query_repo->fetchOne("SELECT bldg_id, bldg_name FROM bldg_table WHERE bldg_id = ?", $bldgId);
    if (!$existing) {
        $query_repo->execute("INSERT INTO bldg_table (bldg_id, bldg_name) VALUES (?, ?)", $bldgId, $bldgName);
        echo "<br>Building not found — added automatically<br>";
        return;
    }
    if ($existing['bldg_id'] !== $bldgId) {
        $query_repo->execute("UPDATE bldg_table SET bldg_id = ? WHERE bldg_name = ?", $bldgId, $bldgName);
        echo "<br>Bldg ID mismatch — fixed<br>";
    }
    if ($existing['bldg_name'] !== $bldgName) {
        $query_repo->execute("UPDATE bldg_table SET bldg_name = ? WHERE bldg_id = ?", $bldgName, $bldgId);
        echo "<br>Bldg name mismatch — fixed<br>";
    }
}

/**
 * Returns the room_tag for a given building and room location,
 * inserting the room first if it does not exist.
 */
function upsertRoom(int $bldgId, string $roomLoc): int
{
    global $query_repo;

    $room = $query_repo->fetchOne(
        "SELECT room_tag FROM room_table WHERE bldg_id = ? AND room_loc = ?",
        $bldgId,
        $roomLoc
    );
    if (!$room) {
        $query_repo->execute("INSERT INTO room_table (room_loc, bldg_id) VALUES (?, ?)", $roomLoc, $bldgId);
        echo "<br>Inserted room into database<br>";
        $room = $query_repo->fetchOne(
            "SELECT room_tag FROM room_table WHERE bldg_id = ? AND room_loc = ?",
            $bldgId,
            $roomLoc
        );
    }
    return (int)$room['room_tag'];
}

/**
 * Updates an asset's department and room based on building/room information.
 * Returns true on success, false if the building could not be resolved.
 */
function transferAssetLocation(string $tag, string $deptId, int $bldgId, string $bldgName, string $roomLoc): bool
{
    global $query_repo;

    if (empty($bldgId) || empty($bldgName)) {
        echo "<br>Building name or ID not found — skipping<br>";
        return false;
    }

    upsertBuilding($bldgId, $bldgName);
    $roomTag = upsertRoom($bldgId, $roomLoc);

    $asset = $query_repo->fetchOne("SELECT asset_tag FROM asset_info WHERE asset_tag = ?", $tag);
    if (!$asset) {
        echo "<br>Tag not in database<br>";
        return false;
    }

    echo "<br>Bldg ID $bldgId  Bldg Name $bldgName  Room $roomLoc<br>";
    $query_repo->execute(
        "UPDATE asset_info SET dept_id = ?, room_tag = ? WHERE asset_tag = ?",
        $deptId,
        $roomTag,
        $tag
    );
    echo "<br>Updated tag in database<br>";
    return true;
}

/**
 * Ensures a user record exists for the given email.
 * Creates the user with a temporary password if not found.
 * Updates missing profile fields (school_id, form_id, signature) if the record exists.
 * Appends dept_id to the user's department list when the role is custodian.
 */
function upsertUser(
    string $username,
    string $email,
    string $formId,
    string $schoolId,
    string $signature,
    string $fullName,
    string $role,
    string $deptId
): void {
    global $query_repo;

    $user = $query_repo->fetchOne(
        'SELECT username, email, form_id, signature, school_id FROM user_table WHERE email = ?',
        $email
    );

    if ($user) {
        if (empty($user['school_id']) || empty($user['form_id']) || empty($user['signature'])) {
            $query_repo->execute(
                'UPDATE user_table SET school_id = ?, form_id = ?, signature = ? WHERE email = ?',
                $schoolId,
                $formId,
                $signature,
                $email
            );
        }
        if ($role === 'custodian') {
            appendDeptToUser($email, $deptId);
        }
        return;
    }

    $nameParts = explode(' ', $fullName);
    $firstName = $nameParts[0];
    $lastName  = implode(' ', array_slice($nameParts, 1));
    $hashedPw  = password_hash(generateTempPassword(), PASSWORD_DEFAULT);
    $deptArray = '{' . $deptId . '}';

    $query_repo->execute(
        'INSERT INTO user_table (username, pw, email, u_role, f_name, l_name, dept_id, form_id, school_id, signature)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        $username,
        $hashedPw,
        $email,
        $role,
        $firstName,
        $lastName,
        $deptArray,
        $formId,
        $schoolId,
        $signature
    );
}

/**
 * Appends a dept_id to a user's dept_id array if not already present.
 */
function appendDeptToUser(string $email, string $deptId): void
{
    global $query_repo;

    $alreadyLinked = $query_repo->fetchOne(
        'SELECT 1 FROM user_table WHERE ? = ANY(dept_id) AND email = ?',
        $deptId,
        $email
    );
    if (!$alreadyLinked) {
        $query_repo->execute(
            'UPDATE user_table SET dept_id = ARRAY_APPEND(dept_id, ?) WHERE email = ?',
            $deptId,
            $email
        );
    }
}

/**
 * Ensures a department record exists and that its custodian list is current.
 * Inserts the department if missing; updates the manager and custodian list if present.
 */
function upsertDepartment(
    string $deptId,
    string $deptName,
    string $custodianName,
    string $managerName,
    string $documentSetId,
    string $deptKualiId
): void {
    global $query_repo;

    $dept = $query_repo->fetchOne(
        'SELECT dept_id, dept_manager FROM department WHERE dept_id = ?',
        $deptId
    );

    if (!$dept) {
        $query_repo->execute(
            'INSERT INTO department (dept_id, dept_name, custodian, dept_manager, document_set_id, form_id)
             VALUES (?, ?, ?, ?, ?, ?)',
            $deptId,
            $deptName,
            '{' . $custodianName . '}',
            $managerName,
            $documentSetId,
            $deptKualiId
        );
        return;
    }

    if ($dept['dept_manager'] !== $managerName) {
        $query_repo->execute(
            'UPDATE department SET dept_manager = ? WHERE dept_id = ?',
            $managerName,
            $deptId
        );
    }

    $custodianDepts = $query_repo->fetchAll(
        'SELECT dept_id FROM department WHERE ? = ANY(custodian)',
        $custodianName
    );
    $custodianInDept = array_filter($custodianDepts, fn($row) => $row['dept_id'] === $deptId);

    if (empty($custodianInDept)) {
        $query_repo->execute(
            'UPDATE department SET custodian = ARRAY_APPEND(custodian, ?) WHERE dept_id = ?',
            $custodianName,
            $deptId
        );
    }
}

/**
 * Updates the audit history record for a department, rolling forward to a new audit cycle ID.
 */
function updateOldAudit(string $deptId, int $auditId, int $newAuditId): void
{
    global $query_repo;
    $query_repo->execute(
        "UPDATE audit_history SET audit_status = 'Complete', audit_id = ? WHERE dept_id = ? AND audit_id = ?",
        $newAuditId,
        $deptId,
        $auditId
    );
}

/**
 * Updates the custodian and manager on a department record,
 * appending the custodian to the array if not already listed.
 */
function syncDepartmentPersonnel(string $deptId, string $custodian, string $manager): void
{
    global $query_repo;

    $custodianListed = $query_repo->fetchOne(
        'SELECT 1 FROM department WHERE ? = ANY(custodian) AND dept_id = ?',
        $custodian,
        $deptId
    );

    if (!$custodianListed) {
        $query_repo->execute(
            'UPDATE department SET custodian = ARRAY_APPEND(custodian, ?), dept_manager = ? WHERE dept_id = ?',
            $custodian,
            $manager,
            $deptId
        );
    } else {
        $query_repo->execute(
            'UPDATE department SET dept_manager = ? WHERE dept_id = ?',
            $manager,
            $deptId
        );
    }
}

/**
 * Handles the audit ID routing logic shared by completeAudit and dwCompleteAudit.
 * Routes to updateOldAudit for management/SPA/self cycles, or marks directly complete.
 */
function resolveAuditCompletion(string $deptId, array $auditIds, array $auditFreq, string $formId): void
{
    global $query_repo;

    $auditId = (int)($auditIds['audit_id'] ?? 0);

    if ($auditId === 6) {
        $prevMgmt = ($auditFreq['curr_mgmt_id'] == 4) ? 5 : $auditFreq['curr_mgmt_id'];
        updateOldAudit($deptId, $auditId, $prevMgmt);
    } elseif ($auditId === 9) {
        $prevSpa = ($auditFreq['curr_spa_id'] == 7) ? 8 : $auditFreq['curr_spa_id'];
        updateOldAudit($deptId, $auditId, $prevSpa);
    } elseif ($auditId === 3) {
        $prevSelf = ($auditFreq['curr_self_id'] == 1) ? 2 : $auditFreq['curr_self_id'];
        updateOldAudit($deptId, $auditId, $prevSelf);
    } else {
        $query_repo->execute(
            "UPDATE audit_history SET audit_status = 'Complete' WHERE complete_form_id = ?",
            $formId
        );
    }
}


// ===========================================================================
// Kuali sync functions
// ===========================================================================

/**
 * Removes audit schedules whose date has already passed.
 */
function deleteOverdueSchedule(): void
{
    global $query_repo;
    echo '<br>Delete Overdue Schedule<br>';
    $query_repo->execute('DELETE FROM audit_schedule WHERE audit_date < CURRENT_TIMESTAMP');
}

/**
 * Syncs custodian responsibility records from Kuali,
 * upserting users, signatures, and department associations.
 */
function addKualiInfo(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>Add Kuali Info<br>';

    $skip  = (int)($result['cust_responsibility_time'] ?? 0);
    $edges = $kuali->baseReads(APP_CUST_RESPONSIBILITY, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;
            $depts = resolveDeptArray($edge);

            foreach ($depts as $deptNode) {
                [$deptId, $deptName, $documentSetId, $deptKualiId] = extractDeptFields($deptNode);

                processCustodianSignatureNode($edge, $deptId);
                processManagerSignatureNode($edge, $deptId);
                processManagerInfoNode($edge, $deptId);
                processCustodianInfoNode($edge, $deptId);

                $custodianName = isset($edge['node']['data']['XhBe3DNaU4'])
                    ? parseDisplayName($edge['node']['data']['XhBe3DNaU4']['displayName'])['fullName']
                    : '';
                $managerName = isset($edge['node']['data']['04PgxWqAbE'])
                    ? parseDisplayName($edge['node']['data']['04PgxWqAbE']['displayName'])['fullName']
                    : '';

                upsertDepartment($deptId, $deptName, $custodianName, $managerName, $documentSetId, $deptKualiId);
            }
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }

    $query_repo->execute("UPDATE kuali_table SET cust_responsibility_time = ?", $skip);
}

/**
 * Returns a normalized list of department nodes from a Kuali edge,
 * handling all three possible edge structures (single dept or multi-dept arrays).
 */
function resolveDeptArray(array $edge): array
{
    $data = $edge['node']['data'];

    if (isset($data['r4XeMIe7yh']['data'][0]['data']['Gsxde2JR77']['data'][KUALI_DEPT_ID])) {
        return $data['r4XeMIe7yh']['data'];
    }
    if (isset($data['HBG7HehhU8']['data'][0]['data']['HN8JcizYYj']['data'][KUALI_DEPT_ID])) {
        return $data['HBG7HehhU8']['data'];
    }

    // Single department — wrap in an array so callers iterate uniformly
    return [['_single' => true, '_edge' => $edge]];
}

/**
 * Extracts dept_id, dept_name, documentSetId, and dept_kuali_id from a dept node.
 * Handles both multi-dept array nodes and the single-dept edge format.
 */
function extractDeptFields(array $deptNode): array
{
    // Single-dept path
    if (!empty($deptNode['_single'])) {
        $d = $deptNode['_edge']['node']['data']['XeTTtfl6XW'];
        return [
            $d['data'][KUALI_DEPT_ID],
            $d['data'][KUALI_DEPT_NAME],
            $d['documentSetId'],
            $d['id'],
        ];
    }

    // Multi-dept path — Gsxde2JR77 variant
    if (isset($deptNode['data']['Gsxde2JR77']['data'][KUALI_DEPT_ID])) {
        $d = $deptNode['data']['Gsxde2JR77'];
        return [$d['data'][KUALI_DEPT_ID], $d['data'][KUALI_DEPT_NAME], $d['documentSetId'], $d['id']];
    }

    // Multi-dept path — HN8JcizYYj variant
    $d = $deptNode['data']['HN8JcizYYj'];
    return [$d['data'][KUALI_DEPT_ID], $d['data'][KUALI_DEPT_NAME], $d['documentSetId'], $d['id']];
}

/**
 * Processes and upserts the custodian signature node from a Kuali edge (key XhBe3DNaU4).
 */
function processCustodianSignatureNode(array $edge, string $deptId): void
{
    $data = $edge['node']['data'];
    if (!isset($data['XhBe3DNaU4'])) return;

    $parsed    = parseDisplayName($data['XhBe3DNaU4']['displayName']);
    $signature = resolveSignature($data['XhBe3DNaU4'], $parsed['fullName']);
    $schoolId  = $data['kS_kp-Oo1y']['schoolId'];
    $formId    = $data['XhBe3DNaU4']['userId'];

    echo "<br>Custodian Signature: {$parsed['fullName']} | {$parsed['email']}<br>";
    upsertUser($parsed['username'], $parsed['email'], $formId, $schoolId, $signature, $parsed['fullName'], 'custodian', $deptId);
}

/**
 * Processes and upserts the manager/dean signature node from a Kuali edge (key 04PgxWqAbE).
 */
function processManagerSignatureNode(array $edge, string $deptId): void
{
    $data = $edge['node']['data'];
    if (!isset($data['04PgxWqAbE'])) return;

    $parsed    = parseDisplayName($data['04PgxWqAbE']['displayName']);
    $signature = resolveSignature($data['04PgxWqAbE'], $parsed['fullName']);
    $schoolId  = $data['jTxoK_Wsh7']['schoolId'];
    $formId    = $data['04PgxWqAbE']['userId'];

    echo "<br>Manager Signature: {$parsed['fullName']} | {$parsed['email']}<br>";
    upsertUser($parsed['username'], $parsed['email'], $formId, $schoolId, $signature, $parsed['fullName'], 'user', $deptId);
}

/**
 * Processes and upserts the manager info node from a Kuali edge (key jTxoK_Wsh7).
 */
function processManagerInfoNode(array $edge, string $deptId): void
{
    $data = $edge['node']['data'];
    if (!isset($data['jTxoK_Wsh7'])) return;

    $node      = $data['jTxoK_Wsh7'];
    $email     = $node['email'];
    $username  = explode('@', $email)[0];
    $fullName  = $node['displayName'];

    echo "<br>Manager Info: $fullName | $email<br>";
    upsertUser($username, $email, $node['id'], $node['schoolId'], $fullName, $fullName, 'user', $deptId);
}

/**
 * Processes and upserts the custodian info node from a Kuali edge (key kS_kp-Oo1y).
 */
function processCustodianInfoNode(array $edge, string $deptId): void
{
    $data = $edge['node']['data'];
    if (!isset($data['kS_kp-Oo1y'])) return;

    $node      = $data['kS_kp-Oo1y'];
    $email     = $node['email'];
    $username  = explode('@', $email)[0];
    $fullName  = $node['displayName'];

    echo "<br>Custodian Info: $fullName | $email<br>";
    upsertUser($username, $email, $node['id'], $node['schoolId'], $fullName, $fullName, 'custodian', $deptId);
}

/**
 * Syncs new asset addition records from Kuali into asset_info.
 */
function assetAddition(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>Asset Addition<br>';

    $skip  = (int)($result['asset_addition_time'] ?? 0);
    $edges = $kuali->baseReads(APP_ASSET_ADDITION, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;
            if (!isset($edge['node']['data']['PUcYspMrJZ'])) {
                echo "<br>Skipping — tag not available<br>";
                continue;
            }

            $tagData      = $edge['node']['data']['PUcYspMrJZ']['data'];
            $profileLabel = $edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['pZEr8FpYK_']['label'] ?? 'EQUIP-10';

            if ($profileLabel === 'SOFTWARE' || $profileLabel === 'BLDGIMP') {
                echo "<br>Skipping profile: $profileLabel<br>";
                continue;
            }

            $lifecycle = ASSET_PROFILE_MAP[$profileLabel] ?? 10;
            $rawValue  = $edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['PxtY2-Q3bL'] ?? '100';
            $value     = (float)substr_replace($rawValue, '.', strlen($rawValue) - 2, 0);
            $deptId    = $edge['node']['data']['tdCq6KU0B2']['data'][0]['data']['WZ5fZCt1qz']['data'][KUALI_DEPT_ID] ?? null;

            foreach ($tagData as $tag) {
                $tagNum = $tag['data']['hYk-CuEHw-'];
                if (!isValidTagFormat($tagNum)) continue;

                if (isset($tag['data']['XGD63KvFDV']['data'][KUALI_DEPT_ID])) {
                    $deptId = $tag['data']['XGD63KvFDV']['data'][KUALI_DEPT_ID];
                }

                $serialNum = $tag['data']['TuFLyAwO61'] ?? 'Unknown';
                $name      = $tag['data']['6dtRzO-_qZ'] ?? $tag['data']['wnpc592QUl'];
                $fund      = $tag['data']['TSeIOwwu6t'];
                $itStatus  = isItEquipment($name);

                $exists = $query_repo->fetchOne("SELECT asset_tag FROM asset_info WHERE asset_tag = ?", $tagNum);
                if ($exists) {
                    echo "<br>Tag $tagNum already exists — skipping<br>";
                    continue;
                }

                $date = date('m-d-y', $edge['node']['meta']['workflowCompletedAt'] / 1000);
                $query_repo->execute(
                    "INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, dept_id, lifecycle, po, is_IT, fund)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    $tagNum,
                    $name,
                    $date,
                    $serialNum,
                    $value,
                    $deptId,
                    $lifecycle,
                    1,
                    $itStatus,
                    $fund
                );
                echo "<br>Added — Tag: $tagNum | Name: $name | Dept: $deptId | Value: $value<br>";
            }
        }
    } catch (PDOException $e) {
        error_log("assetAddition error: " . $e->getMessage());
        return;
    }

    $query_repo->execute("UPDATE kuali_table SET asset_addition_time = ?", $skip);
}

/**
 * Syncs asset received records from Kuali into asset_info.
 */
function assetReceived(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>Asset Received<br>';

    $skip        = (int)($result['asset_received_time'] ?? 0);
    $edges       = $kuali->baseReads(APP_ASSET_RECEIVED, $skip)['data']['app']['documentConnection']['edges'];
    $defaultRoom = 2051;

    try {
        foreach ($edges as $edge) {
            $skip++;
            $data      = $edge['node']['data'];
            $fund      = $data['di6FE1yIML'];
            $date      = date("Y-m-d", (int)$data['wzgp7QHb7F'] / 1000);
            $deptId    = $data['KMudjEpsXS']['data'][KUALI_DEPT_ID];
            $model     = $data['L6q0gWhZ-Q']['label'];
            $tagData   = $data['0nVFqyLknC']['data'];

            $poRaw = $data['3BdpFK5t1I'];
            $po    = preg_match('/order/i', $poRaw) ? 0 : (int)$poRaw;

            if (strcasecmp($model, 'Other') === 0) {
                $model = trim($_POST['vendor_name'] ?? 'Other');
            }

            foreach ($tagData as $tag) {
                $tagNum = $tag['data']['1SI4ghT1Jt'] ?? '';
                if (!isValidTagFormat($tagNum)) continue;

                $serialNum = $tag['data']['Wrnezf-g0C'] ?? '';
                $rawValue  = $tag['data']['he_zIFgDiT'];
                $value     = (float)substr_replace($rawValue, '.', strlen($rawValue) - 2, 0);
                $name      = $tag['data']['vNv8CdzZjv'];

                if (isEmptyTag($tagNum) || empty($po) || empty($deptId) || empty($serialNum) || empty($name)) {
                    continue;
                }

                $exists = $query_repo->fetchOne("SELECT asset_tag FROM asset_info WHERE asset_tag = ?", $tagNum);
                if ($exists) {
                    echo $exists['asset_tag'] . " already exists<br>";
                    continue;
                }

                try {
                    $query_repo->execute(
                        "INSERT INTO asset_info (asset_tag, asset_name, date_added, serial_num, asset_price, asset_model, po, dept_id, lifecycle, room_tag, is_IT, fund)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        $tagNum,
                        $name,
                        $date,
                        $serialNum,
                        $value,
                        $model,
                        $po,
                        $deptId,
                        10,
                        $defaultRoom,
                        isItEquipment($model),
                        $fund
                    );
                    echo "<br>Inserted — Tag: $tagNum | SN: $serialNum | Value: $value | Name: $name<br>";
                } catch (PDOException $e) {
                    echo "<br>Insert failed — Tag: $tagNum | Error: " . $e->getMessage() . "<br>";
                }
            }
        }

        $query_repo->execute("UPDATE kuali_table SET asset_received_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes bulk PSR (Property Survey Report) disposals from Kuali.
 */
function bulkPsr(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>Bulk PSR<br>';

    $skip  = (int)($result['bulk_psr_time'] ?? 0);
    $edges = $kuali->baseReads(APP_BULK_PSR, $skip)['data']['app']['documentConnection']['edges'];
    $count = 1;

    try {
        foreach ($edges as $edge) {
            $skip++;
            $tagData = $edge['node']['data']['DtFI8bQn4g']['data'] ?? null;
            if (!$tagData) {
                echo "<br>Skipping — no tag data<br>";
                continue;
            }

            foreach ($tagData as $item) {
                $tag = $item['data']['6_z3IcanWR'];
                if (isEmptyTag($tag)) {
                    echo "<br>Empty tag — skipping<br>";
                    continue;
                }
                disposeAsset($tag);
                echo "<br>[$count] Disposed tag: $tag<br>";
                $count++;
            }
        }

        $query_repo->execute("UPDATE kuali_table SET bulk_psr_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes bulk asset transfers between departments from Kuali.
 */
function bulkTransfer(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>Bulk Transfer<br>';

    $skip  = (int)($result['bulk_transfer_time'] ?? 0);
    $edges = $kuali->baseReads(APP_BULK_TRANSFER, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;
            echo "<br>Counter: $skip<br>";

            if (trim($edge['node']['data']['_GODY1FjEy']['label']) !== 'From one department to another department') {
                echo $edge['node']['data']['_GODY1FjEy']['label'] . "<br>";
                continue;
            }

            foreach ($edge['node']['data']['JZ-q3J19dw']['data'] as $item) {
                $tag = $item['data']['RxpLOF3XrE'];
                if (isEmptyTag($tag)) {
                    echo "<br>Empty tag — skipping<br>";
                    continue;
                }

                $deptId  = substr($item['data']['5c3qSm88bs'], 0, 6);
                $roomLoc = $item['data']['6JHs3W0-CL'] ?? null;

                [$bldgId, $bldgName] = resolveBuildingFromTransferItem($item);

                echo "<br>Tag: $tag | Dept: $deptId<br>";
                transferAssetLocation($tag, $deptId, $bldgId, $bldgName, $roomLoc);
                $query_repo->execute("UPDATE kuali_table SET bulk_transfer_time = ?", $skip);
                echo "<br>---<br>";
            }
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Resolves building ID and name from a bulk-transfer item node.
 * Handles two possible field key variants.
 */
function resolveBuildingFromTransferItem(array $item): array
{
    $bldgId   = null;
    $bldgName = null;

    if (!empty($item['data']['bYpfsUDuZx']['data'][KUALI_DEPT_ID])) {
        $bldgId   = $item['data']['bYpfsUDuZx']['data'][KUALI_DEPT_ID];
        $bldgName = $item['data']['bYpfsUDuZx']['data'][KUALI_DEPT_NAME];
    }
    if (!empty($item['data']['BC0E2hOKv3']['data'][KUALI_DEPT_ID])) {
        $bldgId   = $item['data']['BC0E2hOKv3']['data'][KUALI_DEPT_ID];
        $bldgName = $item['data']['BC0E2hOKv3']['data'][KUALI_DEPT_NAME];
    }

    if ($bldgId === '39A') $bldgId = 39;

    return [(int)$bldgId, $bldgName];
}

/**
 * Processes equipment check-out and check-in records from Kuali.
 */
function checkOut(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>Check Out<br>';

    $skip  = (int)($result['check_out_time'] ?? 0);
    $edges = $kuali->baseReads(APP_CHECK_OUT, $skip)['data']['app']['documentConnection']['edges'];
    $count = 1;

    try {
        foreach ($edges as $edge) {
            $skip++;
            echo "<br>Counter: $skip<br>";

            $data          = $edge['node']['data'];
            $checkOutType  = $data['fyaCF8g3Uh']['label'];
            $tag           = $data['AvjKneaxPz'][1]['jswe8fMFPT'] ?? $data['BOZIA6hewQ'];

            if (isEmptyTag($tag)) {
                echo "<br>Empty tag — skipping<br>";
                continue;
            }

            $isCheckOut = ($checkOutType === 'Checking Out Equipment');

            $asset = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ?", $tag);
            if ($asset) {
                if ($isCheckOut) {
                    $dept     = $data['isFMbCuv8e']['data'][KUALI_DEPT_ID] ?? 'Unknown Dept';
                    $borrower = $data['JsHBzpz-AT']['displayName'] ?? $data['JXLJ_AOov-']['displayName'];
                    $parts    = explode(' ', $borrower);
                    $borrower = $parts[0] . ' ' . $parts[count($parts) - 2];
                    $note     = "CHCKD,$dept $borrower";
                    $query_repo->execute("UPDATE asset_info SET asset_notes = ? WHERE asset_tag = ?", $note, $tag);
                } else {
                    $query_repo->execute("UPDATE asset_info SET asset_notes = NULL WHERE asset_tag = ?", $tag);
                }
            } else {
                $query_repo->execute(
                    'INSERT INTO asset_info (asset_tag, asset_name, type2, serial, dept_id) VALUES (?,?,?,?,?)',
                    $tag,
                    $data['cQOz4UQ0rQ'],
                    $data['aUVT1BLN6V'],
                    $data['jYTHHgL10M'],
                    $data['isFMbCuv8e']['data'][KUALI_DEPT_ID] ?? 'Unknown Dept'
                );
            }

            echo "<br>[$count] Tag: $tag<br>";
            $count++;
        }

        $query_repo->execute("UPDATE kuali_table SET check_out_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes lost/stolen/destroyed (LSD) asset records from Kuali.
 */
function lsd(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>LSD<br>';

    $skip  = (int)($result['equip_lost_stol_time'] ?? 0);
    $edges = $kuali->baseReads(APP_LSD, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $tag = $edge['node']['data']['y7nFCmsLEg'] ?? $edge['node']['data']['ufHf4QAJsc'];
            echo "<br>Tag: $tag<br>";
            disposeAsset($tag);
            $skip++;
            $query_repo->execute("UPDATE kuali_table SET equip_lost_stol_time = ?", $skip);
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes PSR (Property Survey Report) single-item disposal records from Kuali.
 */
function psr(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>PSR<br>';

    $skip  = (int)($result['psr_time'] ?? 0);
    $edges = $kuali->baseReads(APP_PSR, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;
            foreach ($edge['node']['data']['W_Uw0hSpff']['data'] as $item) {
                $tag = $item['data']['yks38VOkzw'];
                echo "<br>Tag: $tag<br>";
                disposeAsset($tag);
            }
        }
        $query_repo->execute("UPDATE kuali_table SET psr_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Routes property transfer records from Kuali to the appropriate handler
 * based on transfer type (department change, bus number change, or building change).
 */
function propertyTransfer(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>Property Transfer<br>';

    $skip  = (int)($result['transfer_time'] ?? 0);
    $edges = $kuali->baseReads(APP_PROPERTY_TRANSFER, $skip)['data']['app']['documentConnection']['edges'];

    foreach ($edges as $edge) {
        $skip++;
        foreach ($edge['node']['data']['-SBfvXlL1f'] as $type) {
            if ($type['id'] === TRANSFER_TYPE_DEPT) {
                deptChange($edge);
            } elseif ($type['id'] === TRANSFER_TYPE_BUS) {
                busChange($edge);
            } else {
                bldgChange($edge);
            }
        }
    }

    $query_repo->execute("UPDATE kuali_table SET transfer_time = ?", $skip);
}

/**
 * Handles bus number (asset tag) change transfers — renames asset tags.
 */
function busChange(array $edge): void
{
    global $query_repo;
    foreach ($edge['node']['data']['K6NZgw5Vgh']['data'] as $tag) {
        $oldTag = $tag['data']['biJxrXUqIw'];
        $newTag = $tag['data']['XQH80E5rNZ'];
        $query_repo->execute('UPDATE asset_info SET asset_tag = ? WHERE asset_tag = ?', $newTag, $oldTag);
    }
}

/**
 * Handles department change transfers — updates asset dept_id and room,
 * and syncs the department record.
 */
function deptChange(array $edge): void
{
    global $query_repo;

    $manager   = $edge['node']['data']['OdeViTatve']['displayName'];
    $custodian = $edge['node']['data']['9Zu2I3A53B']['displayName'];

    foreach ($edge['node']['data']['t7mH-1FlaO']['data'] as $item) {
        $tag = $item['data']['XZlIFEDX6Y'];
        if (isEmptyTag($tag)) {
            echo "<br>Empty tag — skipping<br>";
            continue;
        }

        $deptId   = extractDeptIdFromTransferItem($item);
        $deptName = extractDeptNameFromTransferItem($item);
        $roomLoc  = $item['data']['zZztPX8Pcw'] ?? $item['data']['CeMwzz3mnp'] ?? $item['data']['6JHs3W0-CL'] ?? null;
        $bldgName = $item['data']['hXHmCy0mek']['label'] ?? $item['data']['YtHlHUNY_q']['label'] ?? null;

        echo "<br>Bldg: $bldgName | Dept: $deptId | Room: $roomLoc<br>";

        if (!empty($bldgName) && !empty($roomLoc)) {
            $bldg = $query_repo->fetchOne('SELECT bldg_id FROM bldg_table WHERE bldg_name = ?', $bldgName);
            if ($bldg) {
                $roomTag = upsertRoom((int)$bldg['bldg_id'], $roomLoc);
                $query_repo->execute('UPDATE asset_info SET room_tag = ? WHERE asset_tag = ?', $roomTag, $tag);
            }
        }

        $deptId = substr($deptId, 0, 6);
        if (!preg_match('/^D/', $deptId)) continue;

        echo "<br>Dept ID format valid<br>";
        $query_repo->execute("UPDATE asset_info SET dept_id = ? WHERE asset_tag = ?", $deptId, $tag);

        $deptExists = $query_repo->fetchOne('SELECT dept_name FROM department WHERE dept_id = ?', $deptId);
        if (!$deptExists) {
            $query_repo->execute(
                'INSERT INTO department (dept_id, dept_name, custodian, dept_manager) VALUES (?, ?, ?, ?)',
                $deptId,
                $deptName,
                $custodian,
                $manager
            );
        } else {
            syncDepartmentPersonnel($deptId, $custodian, $manager);
        }

        echo "<br>---<br>";
    }
}

/**
 * Handles building-only transfers — updates room location without changing department.
 */
function bldgChange(array $edge): void
{
    global $query_repo;

    foreach ($edge['node']['data']['t7mH-1FlaO']['data'] as $item) {
        $tag = $item['data']['XZlIFEDX6Y'];
        if (isEmptyTag($tag)) {
            echo "<br>Empty tag — skipping<br>";
            continue;
        }

        $roomLoc  = $item['data']['zZztPX8Pcw'] ?? $item['data']['CeMwzz3mnp'] ?? $item['data']['6JHs3W0-CL'] ?? null;
        $bldgName = $item['data']['hXHmCy0mek']['label'] ?? $item['data']['YtHlHUNY_q']['label'] ?? null;

        echo "<br>Bldg: $bldgName | Room: $roomLoc<br>";

        if (!empty($bldgName) && !empty($roomLoc)) {
            $bldg = $query_repo->fetchOne('SELECT bldg_id FROM bldg_table WHERE bldg_name = ?', $bldgName);
            if ($bldg) {
                $roomTag = upsertRoom((int)$bldg['bldg_id'], $roomLoc);
                $query_repo->execute('UPDATE asset_info SET room_tag = ? WHERE asset_tag = ?', $roomTag, $tag);
            }
        }

        echo "<br>---<br>";
    }
}

/**
 * Returns the dept_id from a transfer item, checking both possible field key variants.
 */
function extractDeptIdFromTransferItem(array $item): ?string
{
    return $item['data']['U73d7kPH5b']['data'][KUALI_DEPT_ID]
        ?? $item['data']['qvczWxUOzQ']['data'][KUALI_DEPT_ID]
        ?? null;
}

/**
 * Returns the dept_name from a transfer item, checking both possible field key variants.
 */
function extractDeptNameFromTransferItem(array $item): ?string
{
    return $item['data']['U73d7kPH5b']['data'][KUALI_DEPT_NAME]
        ?? $item['data']['qvczWxUOzQ']['data'][KUALI_DEPT_NAME]
        ?? null;
}

/**
 * Processes SPA bus number change records from Kuali.
 * TODO: $old_tag source is unclear and needs investigation before enabling.
 */
function spaBusChange(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>SPA Bus Change<br>';

    $skip  = (int)($result['bus_change_time'] ?? 0);
    $edges = $kuali->baseReads(APP_SPA_BUS_CHANGE, $skip)['data']['app']['documentConnection']['edges'];

    foreach ($edges as $edge) {
        $skip++;
        foreach ($edge['node']['data']['z64jO_p-uG']['data'] as $item) {
            $tag     = $item['data']['ep7IXpogXq'];
            $newTag  = ''; // TODO: resolve source field
            $newName = ''; // TODO: resolve source field
            $oldTag  = ''; // TODO: resolve source field

            $exists = $query_repo->fetchOne('SELECT asset_tag FROM asset_info WHERE asset_tag = ?', $tag);
            if ($exists) {
                $query_repo->execute('UPDATE asset_info SET asset_tag = ? WHERE asset_tag = ?', $newTag, $oldTag);
            } else {
                $query_repo->execute(
                    'INSERT INTO asset_info (asset_tag, asset_name, serial_num) VALUES (?, ?, ?)',
                    $newTag,
                    $newName,
                    'N/A'
                );
            }

            $query_repo->execute('UPDATE kuali_table SET bus_change_time = ?', $skip);
        }
    }
}

/**
 * Processes DW bulk asset transfers between departments from Kuali.
 * Differs from bulkTransfer in how building name is parsed from the data.
 */
function dwBulkTransfer(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>DW Bulk Transfer<br>';

    $skip  = (int)($result['dw_bulk_time'] ?? 0);
    $edges = $kuali->baseReads(APP_DW_BULK_TRANSFER, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;

            if (trim($edge['node']['data']['_GODY1FjEy']['label']) !== 'From one department to another department') {
                echo $edge['node']['data']['_GODY1FjEy']['label'] . "<br>";
                continue;
            }

            foreach ($edge['node']['data']['JZ-q3J19dw']['data'] as $item) {
                $tag = $item['data']['RxpLOF3XrE'];
                if (isEmptyTag($tag)) {
                    echo "<br>Empty tag — skipping<br>";
                    continue;
                }

                $deptId  = substr($item['data']['5c3qSm88bs'], 0, 6);
                $roomLoc = $item['data']['6JHs3W0-CL'] ?? null;

                // DW format encodes building as "Label (BuildingName)"
                $rawBldg  = $item['data']['SBu1DONXk2'];
                $bldgName = str_replace(')', '', explode('(', $rawBldg)[1]);
                $bldg     = $query_repo->fetchOne("SELECT bldg_id FROM bldg_table WHERE bldg_name = ?", $bldgName);
                $bldgId   = (int)($bldg['bldg_id'] ?? 0);

                echo "<br>Tag: $tag | Dept: $deptId<br>";
                transferAssetLocation($tag, $deptId, $bldgId, $bldgName, $roomLoc);
                echo "<br>---<br>";
            }
        }

        $query_repo->execute("UPDATE kuali_table SET dw_bulk_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes DW equipment check-out and check-in records from Kuali.
 */
function dwCheckOut(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>DW Check Out<br>';

    $skip  = (int)($result['dw_check_time'] ?? 0);
    $edges = $kuali->baseReads(APP_DW_CHECK_OUT, $skip)['data']['app']['documentConnection']['edges'];
    $count = 1;

    try {
        foreach ($edges as $edge) {
            $skip++;
            $data         = $edge['node']['data'];
            $checkOutType = $data['fyaCF8g3Uh']['label'];
            $tag          = $data['AvjKneaxPz'][1]['jswe8fMFPT'] ?? $data['BOZIA6hewQ'];

            if (isEmptyTag($tag)) {
                echo "<br>Empty tag — skipping<br>";
                continue;
            }

            $isCheckOut = ($checkOutType === 'Checking Out Equipment');

            $asset = $query_repo->fetchOne("SELECT 1 FROM asset_info WHERE asset_tag = ?", $tag);
            if ($asset) {
                if ($isCheckOut) {
                    $dept     = $data['isFMbCuv8e']['data'][KUALI_DEPT_ID] ?? 'Unknown Dept';
                    $borrower = $data['JsHBzpz-AT']['displayName'] ?? $data['JXLJ_AOov-']['displayName'];
                    $parts    = explode(' ', $borrower);
                    $borrower = $parts[0] . ' ' . $parts[count($parts) - 2];
                    $note     = "CHCKD,$dept $borrower";
                    $query_repo->execute("UPDATE asset_info SET asset_notes = ? WHERE asset_tag = ?", $note, $tag);
                } else {
                    $query_repo->execute("UPDATE asset_info SET asset_notes = NULL WHERE asset_tag = ?", $tag);
                }
            }

            echo "<br>[$count] Tag: $tag<br>";
            $count++;
        }

        $query_repo->execute("UPDATE kuali_table SET dw_check_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes DW lost/stolen/destroyed records from Kuali.
 */
function dwLsd(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>DW LSD<br>';

    $skip  = (int)($result['dw_lsd_time'] ?? 0);
    $edges = $kuali->baseReads(APP_DW_LSD, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;
            $tag = $edge['node']['data']['y7nFCmsLEg'] ?? $edge['node']['data']['ufHf4QAJsc'];
            echo "<br>Tag: $tag<br>";
            disposeAsset($tag);
        }
        $query_repo->execute("UPDATE kuali_table SET dw_lsd_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes DW PSR disposal records from Kuali.
 */
function dwPsr(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>DW PSR<br>';

    $skip  = (int)($result['dw_psr_time'] ?? 0);
    $edges = $kuali->baseReads(APP_DW_PSR, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;
            foreach ($edge['node']['data']['W_Uw0hSpff']['data'] as $item) {
                $tag = $item['data']['yks38VOkzw'];
                echo "<br>Tag: $tag<br>";
                disposeAsset($tag);
            }
        }
        $query_repo->execute("UPDATE kuali_table SET dw_psr_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes DW LSD v2 disposal records from Kuali (single tag field variant).
 */
function dwLsdV2(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>DW LSD V2<br>';

    $skip  = (int)($result['dw_lsd_time_v2'] ?? 0);
    $edges = $kuali->baseReads(APP_DW_LSD_V2, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;
            $tag = $edge['node']['data']['2iwsFa0_2j'];
            echo "<br>Tag: $tag<br>";
            disposeAsset($tag);
        }
        $query_repo->execute("UPDATE kuali_table SET dw_lsd_time_v2 = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    }
}

/**
 * Polls Kuali to check whether in-progress audit check forms have been completed,
 * and updates the audit_history record accordingly.
 */
function checkFormStatus(): void
{
    global $result, $query_repo, $kuali;

    $formTypeMap = [
        'rlsd'      => APP_DW_LSD_V2,
        'lsd'       => APP_DW_LSD,
        'transfer'  => APP_DW_BULK_TRANSFER,
        'rtransfer' => '68d09e38d599f1028a08969a',
    ];

    $inProgressForms = $query_repo->fetchAll(
        "SELECT unnest(check_forms) AS form_id, dept_id, audit_id
         FROM audit_history
         WHERE check_forms IS NOT NULL AND CAST(check_forms AS TEXT) ILIKE '%in-progress%'"
    );

    foreach ($inProgressForms as $form) {
        $parts = explode(',', $form['form_id']);
        if (count($parts) < 4) continue;

        $documentId = trim($parts[0]);
        $formType   = trim($parts[1]);
        $formStatus = strtolower(trim($parts[2]));
        $queryValue = trim($parts[3]);

        $appId = $formTypeMap[$formType] ?? null;
        if (!$appId) continue;

        $resolvedStatus = $kuali->queryKualiFormStatus($appId, $queryValue);
        if ($resolvedStatus === null) continue;

        if ($resolvedStatus === 'in-progress' || $resolvedStatus === $formStatus) continue;

        if (in_array($resolvedStatus, ['complete', 'withdrawn', 'denied']) && $formStatus === 'in progress') {
            $newForm = str_replace('in-progress', $resolvedStatus, $form['form_id']);
            $query_repo->execute(
                "UPDATE audit_history
                 SET check_forms = array_append(array_remove(COALESCE(check_forms, '{}'::text[]), ?), ?)
                 WHERE audit_id = ? AND dept_id = ?",
                $form['form_id'],
                $newForm,
                $form['audit_id'],
                $form['dept_id']
            );
        }
    }
}

/**
 * Queries the Kuali GraphQL API to find the current workflow status of a specific document.
 * Returns the status string, or null if the document was not found.
 */
function queryKualiFormStatus(string $url, string $apiKey, string $appId, string $documentId, string $queryValue): ?string
{
    $payload = json_encode([
        "query"     => 'query ($appId: ID! $skip: Int! $limit: Int! $sort: [String!] $query: String $fields: Operator) {
            app(id: $appId) {
                id name documentConnection(args: { skip: $skip limit: $limit sort: $sort query: $query fields: $fields } keyBy: ID) {
                    totalCount
                    edges { node { id meta } }
                    pageInfo { hasNextPage hasPreviousPage skip limit }
                }
            }
        }',
        "variables" => [
            "appId"  => $appId,
            "skip"   => 0,
            "limit"  => 100,
            "sort"   => ["meta.updatedAt"],
            "query"  => $queryValue,
            "fields" => [
                "type"      => "OR",
                "operators" => [
                    ["field" => "meta.workflowStatus", "type" => "IS",    "value" => "Complete"],
                    ["field" => "meta.updatedAt",      "type" => "RANGE", "min"   => "0"],
                ],
            ],
        ],
    ]);

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey",
        ],
        CURLOPT_POSTFIELDS => $payload,
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $decoded = json_decode($response, true);
    $edges   = $decoded['data']['app']['documentConnection']['edges'] ?? [];

    foreach ($edges as $edge) {
        if (trim($edge['node']['id']) === $documentId) {
            return $edge['node']['meta']['workflowStatus'] ?? null;
        }
    }

    return null;
}

/**
 * Syncs upcoming audit schedule records from Kuali into audit_schedule.
 * Skips records whose scheduled date has already passed.
 */
function getAuditSchedules(): void
{
    global $kuali, $query_repo;
    echo '<br>Get Audit Schedules<br>';

    $result = $query_repo->fetchOne("SELECT * FROM kuali_table");
    $skip   = (int)($result['schedule_time'] ?? 0);
    $edges  = $kuali->baseReads(APP_AUDIT_SCHEDULE, $skip)['data']['app']['documentConnection']['edges'];

    try {
        foreach ($edges as $edge) {
            $skip++;
            $data      = $edge['node']['data'];
            $timestamp = $data['tYz59qALVK'] + ($data['ChU6eQjeRf'] / 1000);

            if ((int)microtime(true) > $timestamp) continue;

            $date      = (new DateTime("@$timestamp"))->format('Y/m/d H:i:s');
            $custodian = $data['Unwly2UM1p']['displayName'];
            $manager   = $data['epSRSrkGXT']['displayName'] ?? '';

            foreach ($data['G_0VlXBs4s']['data'] ?? [] as $dept) {
                $deptId   = $dept['data']['dTFWWegtgK']['data'][KUALI_DEPT_ID];
                $deptName = $dept['data']['dTFWWegtgK']['data'][KUALI_DEPT_NAME];
                echo "$deptId $deptName<br>";

                if (!empty($manager)) {
                    $query_repo->execute(
                        "INSERT INTO department (dept_id, dept_manager, dept_name, custodian) VALUES (?,?,?,?)
                         ON CONFLICT (dept_id) DO UPDATE SET dept_manager = EXCLUDED.dept_manager",
                        $deptId,
                        $manager,
                        $deptName,
                        '{' . $custodian . '}'
                    );
                }

                $query_repo->execute(
                    'INSERT INTO audit_schedule (dept_id, audit_date, custodian) VALUES (?, ?, ?)',
                    $deptId,
                    $date,
                    $custodian
                );
            }
        }

        $query_repo->execute("UPDATE kuali_table SET schedule_time = ?", $skip);
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
        return;
    } catch (Exception $e) {
        echo "General error: " . $e->getMessage();
        return;
    }
}

/**
 * Processes completed audit forms from Kuali and updates audit_history.
 * (Currently disabled — use dwCompleteAudit instead.)
 */
function completeAudit(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>Complete Audit<br>';

    $skip  = (int)($result['complete_schedule'] ?? 0);
    $edges = $kuali->baseReads(APP_COMPLETE_AUDIT, $skip)['data']['app']['documentConnection']['edges'];

    foreach ($edges as $edge) {
        $skip++;
        $data = $edge['node']['data'];

        if (!isset($data['4Oqb_ktloM']['data'][KUALI_DEPT_ID])) {
            echo 'No department ID for document: ' . $edge['node']['id'] . "<br>";
            continue;
        }

        $deptId   = $data['4Oqb_ktloM']['data'][KUALI_DEPT_ID];
        $deptName = $data['4Oqb_ktloM']['data'][KUALI_DEPT_NAME];

        $deptExists = $query_repo->fetchOne('SELECT dept_id FROM department WHERE dept_id = ?', $deptId);
        if ($deptExists) {
            syncDepartmentPersonnel($deptId, $data['lHuAQy0tZd']['displayName'], $data['55-0zfJWML']['displayName']);

            $auditIds  = $query_repo->fetchOne(
                'SELECT audit_id, dept_id FROM audit_history AS a, unnest(a.check_forms) AS t WHERE t ILIKE ?',
                '%' . $edge['node']['id'] . '%'
            );
            $auditFreq = $query_repo->fetchOne('SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq');

            resolveAuditCompletion($deptId, $auditIds, $auditFreq, $edge['node']['id']);
        }

        $query_repo->execute('UPDATE kuali_table SET complete_schedule = ?', $skip);
        echo "Document: {$edge['node']['id']} | Dept: $deptId | $deptName<br>";
    }
}

function dwCompleteAudit(): void
{
    global $result, $kuali, $query_repo;
    echo '<br>DW Complete Audit<br>';

    $skip  = (int)($result['dw_complete_schedule'] ?? 0);
    $edges = $kuali->baseReads(APP_DW_COMPLETE_AUDIT, $skip)['data']['app']['documentConnection']['edges'];

    foreach ($edges as $edge) {
        $skip++;
        $data = $edge['node']['data'];

        if (!isset($data['Stimf2f9oY']['data'][KUALI_DEPT_ID])) {
            echo 'No department ID for document: ' . $edge['node']['id'] . "<br>";
            continue;
        }

        $deptId   = $data['Stimf2f9oY']['data'][KUALI_DEPT_ID];
        $deptName = $data['Stimf2f9oY']['data'][KUALI_DEPT_NAME];

        $deptExists = $query_repo->fetchOne('SELECT dept_id FROM department WHERE dept_id = ?', $deptId);
        if ($deptExists) {
            syncDepartmentPersonnel($deptId, $data['lHuAQy0tZd']['displayName'], $data['55-0zfJWML']['displayName']);

            $auditIds  = $query_repo->fetchOne(
                'SELECT dept_id, audit_id FROM audit_history WHERE complete_form_id = ?',
                $edge['node']['id']
            );
            $auditFreq = $query_repo->fetchOne('SELECT curr_self_id, curr_mgmt_id, curr_spa_id FROM audit_freq');

            resolveAuditCompletion($deptId, $auditIds ?? [], $auditFreq, $edge['node']['id']);
        }

        echo "Document: {$edge['node']['id']} | Dept: $deptId | $deptName<br>";
        $query_repo->execute('UPDATE kuali_table SET dw_complete_schedule = ?', $skip);
    }
}
