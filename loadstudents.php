<?php
// AJAX endpoint: chỉ trả HTML table, không cần <html><body>
$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_USER = getenv('DB_USER') ?: 'studentuser';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'studentdb';

try {
    $conn = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("DB connection failed: " . $e->getMessage());
}

// Nếu có num_of_student_to_show -> load danh sách số lượng
if (!empty($_POST['num_of_student_to_show'])) {
    $num = (int)$_POST['num_of_student_to_show'];

    $stmt = $conn->prepare("CALL LoadStudents(:num_of_student_to_show)");
    $stmt->bindParam(':num_of_student_to_show', $num, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll();

    echo '<table style="width:100%;margin:0 auto;">';
    echo "<tr>
            <th>MÃ SINH VIÊN</th>
            <th>TÊN SINH VIÊN</th>
            <th>NGÀY SINH</th>
            <th>NƠI SINH</th>
            <th>NGÀNH</th>
          </tr>";

    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['code'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['name'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['dob'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['pob'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['major'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

// Nếu có search_keyword -> search
if (!empty($_POST['search_keyword'])) {
    $keyword = (string)$_POST['search_keyword'];

    $stmt = $conn->prepare("CALL LoadStudentsFullInfoByKeyword(:keyword)");
    $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll();

    echo '<table style="width:100%;margin:0 auto;">';
    echo "<tr>
            <th>MÃ SINH VIÊN</th>
            <th>TÊN SINH VIÊN</th>
            <th>NGÀY SINH</th>
            <th>NƠI SINH</th>
            <th>NGÀNH</th>
          </tr>";

    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['code'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['name'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['dob'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['pob'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['major'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

// Không có input -> không trả gì
http_response_code(204);
exit;
