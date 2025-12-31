<?php
// ===== DB connect from ENV (K8S Secret) =====
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
// ==========================================

if(!empty($_POST['command'])){
    $cmd = $_POST['command'];

    if($cmd == '#loadallsubjects'){   // (đúng theo JS đang gửi)
        $stmt = $conn->prepare("CALL LoadAllCourses()");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<table>";
            echo "<tr>";
                echo "<th>MÃ LỚP HỌC </th>";
                echo "<th>TÊN MÔN HỌC </th>";
                echo "<th>NHÓM</th>";
                echo "<th>ĐỊA ĐIỂM</th>";
                echo "<th>NGÀY</th>";
                echo "<th>KÍP BĐ</th>";
                echo "<th>SỐ TIẾT</th>";
                echo "<th>ACTION</th>";
            echo "</tr>";

            foreach($result as $row){
                $code = $row['code'];
                echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['code']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['group']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['room']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['day_of_week']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['duration_time']) . "</td>";
                    echo "<td><button value='".htmlspecialchars($code)."' class='add_student_to_course'>THÊM SV</button></td>";
                echo "</tr>";
            }
        echo "</table>";

    } else if ($cmd == '#searchcourses'){
        // TODO: nếu JS có search course thì bạn gửi logic, mình bổ sung sau
        echo "<table><tr><td>Search courses chưa implement</td></tr></table>";
    }
}
