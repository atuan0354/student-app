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

if(!empty($_POST['student_code'])){
    $student_code = $_POST['student_code'];

    $stmt = $conn->prepare("CALL LoadResults(:student_code);");
    $stmt->bindParam(':student_code', $student_code, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
        echo "<tr>";
            echo "<th> MÃ ĐIỂM </th>";
            echo "<th> TÊN MÔN HỌC </th>";
            echo "<th> STATUS </th>";
            echo "<th> CC </th>";
            echo "<th> GK </th>";
            echo "<th> BT </th>";
            echo "<th> CUỐI KỲ </th>";
            echo "<th> ĐIỂM SỐ </th>";
            echo "<th> ĐIỂM CHỮ </th>";
            echo "<th> ACTION </th>";
        echo "</tr>";

    foreach($result as $row){
        echo "<tr>";
            echo "<td>" . htmlspecialchars($row['code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['chuyencan']) . "</td>";
            echo "<td>" . htmlspecialchars($row['giuaky']) . "</td>";
            echo "<td>" . htmlspecialchars($row['baitap']) . "</td>";
            echo "<td>" . htmlspecialchars($row['cuoiky']) . "</td>";

            $diem_so = (float)$row['chuyencan']*0.1 + (float)$row['giuaky']*0.2 + (float)$row['baitap']*0.1 + (float)$row['cuoiky']*0.6;
            echo "<td>" . htmlspecialchars((string)$diem_so) . "</td>";

            if($diem_so < 4){
                $diem_chu = 'F';
            } elseif($diem_so < 5){
                $diem_chu = 'D';
            } elseif($diem_so < 5.5){
                $diem_chu = 'D+';
            } elseif($diem_so < 6.0){
                $diem_chu = 'C';
            } elseif($diem_so < 6.5){
                $diem_chu = 'C+';
            } elseif($diem_so < 7.0){
                $diem_chu = 'B';
            } elseif($diem_so < 8.0){
                $diem_chu = 'B+';
            } elseif($diem_so < 9.0){
                $diem_chu = 'A';
            } else {
                $diem_chu = 'A+';
            }

            echo "<td>" . htmlspecialchars($diem_chu) . "</td>";

            $result_value = $row['code'].";".$row['chuyencan'].";".$row['giuaky'].";".$row['baitap'].";".$row['cuoiky'];
            echo "<td><button class=\"modify_result_btn\" value='".htmlspecialchars($result_value)."'>MODIFY</button></td>";
        echo "</tr>";
    }
    echo "</table>";
}
