<?php
// debug_roles.php

// 1. Manually parse .env to get DB credentials
$envPath = __DIR__ . '/.env';
$config = [];

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;

        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            $val = trim($val, '"\'');
            $config[$key] = $val;
        }
    }
}

// 2. Connect to Database
$host = $config['database.default.hostname'] ?? 'localhost';
$user = $config['database.default.username'] ?? 'root';
$pass = $config['database.default.password'] ?? '';
$db   = $config['database.default.database'] ?? 'sdu_okr';

echo "<h2>Debug Roles Investigation</h2>";
$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
echo "✅ Connected to $db<br><br>";

// 3. Investigation: Exact Binary Analysis of Roles
$sql = "SELECT DISTINCT role FROM key_result_departments";
$result = $mysqli->query($sql);

echo "<h3>Role Binary Analysis</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Visible String</th><th>Length</th><th>Hex Dump</th><th>Is 'Leader'?</th></tr>";

while ($row = $result->fetch_assoc()) {
    $role = $row['role'];
    $len = strlen($role);
    $hex = bin2hex($role);
    $isLeader = ($role === 'Leader') ? '✅ YES' : '❌ NO';

    echo "<tr>";
    echo "<td>'<strong>" . htmlspecialchars($role) . "</strong>'</td>";
    echo "<td>$len</td>";
    echo "<td style='font-family:monospace'>$hex</td>";
    echo "<td>$isLeader</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Test Subquery Logic
$year = '2568';
echo "<h3>Subquery Logic Test ($year)</h3>";
$sqlSub = "
SELECT
    d.short_name,
    (SELECT COUNT(DISTINCT kr.id)
     FROM key_result_departments krd
     JOIN key_results kr ON krd.key_result_id = kr.id
     WHERE krd.department_id = d.id
     AND krd.role = 'Leader'
     AND kr.key_result_year = '$year') as leader_count_subquery
FROM departments d
ORDER BY leader_count_subquery DESC
LIMIT 10
";

$resSub = $mysqli->query($sqlSub);
if ($resSub) {
    echo "<table border='1' cellpadding='5'><tr><th>Dept</th><th>Leader Count (Subquery)</th></tr>";
    while ($row = $resSub->fetch_assoc()) {
        echo "<tr><td>" . $row['short_name'] . "</td><td>" . $row['leader_count_subquery'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "Query Error: " . $mysqli->error;
}

// 5. Specific Department Check (Based on User Screenshot: คทท, คมส, คจก)
$targetDepts = ['คทท', 'คมส', 'คจก', 'คดศ', 'คพย'];
$targetList = "'" . implode("','", $targetDepts) . "'";

echo "<h3>Specific Department Check ($targetList) for Year $year</h3>";
$sqlSpec = "
SELECT
    d.short_name,
    krd.role,
    COUNT(kr.id) as count
FROM departments d
JOIN key_result_departments krd ON d.id = krd.department_id
JOIN key_results kr ON krd.key_result_id = kr.id
WHERE d.short_name IN ($targetList)
AND kr.key_result_year = '$year'
GROUP BY d.short_name, krd.role
ORDER BY d.short_name, krd.role
";

$resSpec = $mysqli->query($sqlSpec);

echo "<table border='1' cellpadding='5'><tr><th>Dept</th><th>Role</th><th>Count</th></tr>";
while ($row = $resSpec->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['short_name'] . "</td>";
    echo "<td>'" . htmlspecialchars($row['role']) . "'</td>";
    echo "<td>" . $row['count'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$mysqli->close();
?>
