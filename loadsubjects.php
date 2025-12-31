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

    echo "<table>";
        echo "<thead>";
        echo "<tr>";
            echo "<th> MÃ MÔN HỌC </th>";
            echo "<th> TÊN MÔN HỌC </th>";
            echo "<th> SỐ TÍN CHỈ </th>";
            echo "<th> KHOA </th>";
            echo '<th width=15%> ACTION </th>';
            echo '<th width=15%> C_ACTION </th>';
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

    if($cmd == '#loadallsubjects'){
        $stmt = $conn->prepare("CALL LoadAllSubjects();");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $row){
            echo "<tr>";
                echo "<td>" . htmlspecialchars($row['code']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['num_credit']) . "</td>";
                echo "<td>" . htmlspecialchars($row['major']) . "</td>";
                $code_1559 = $row['code'];
                echo '<td> <input type="button" value="EDIT" name="edit" class="edit_btn">';
                echo '<input type="button" value="SUBMIT" class="submit_btn" style="display:none">';
                echo '<input type="button" value="DISCARD" class="discard_btn" style="display:none">';
                echo '<input type="button" value="DELETE" name="delete" class="delete_btn"> </td>';
                echo '<td><button value="'.htmlspecialchars($code_1559).'" name="create_course_btn" class="create_course_btn">CREATE_COURSE</button></td>';
            echo "</tr>";
        }

    } else if ($cmd == '#searchkeyword'){
        $keyword = $_POST['keyword'] ?? '';
        $stmt = $conn->prepare("CALL LoadSubjectsByKeyword(:keyword);");
        $stmt->bindParam(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($result as $row){
            echo "<tr>";
                echo "<td>" . htmlspecialchars($row['code']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['num_credit']) . "</td>";
                echo "<td>" . htmlspecialchars($row['major']) . "</td>";
                echo '<td> <input type="button" value="EDIT" name="edit" class="edit_btn">';
                echo '<input type="button" value="SUBMIT" class="submit_btn" style="display:none">';
                echo '<input type="button" value="DISCARD" class="discard_btn" style="display:none">';
                echo '<input type="button" value="DELETE" name="delete" class="delete_btn"> </td>';
            echo "</tr>";
        }

    } else {
        echo "<tr><td colspan='6'>exception</td></tr>";
    }

        echo "</tbody>";
    echo "</table>";
}
