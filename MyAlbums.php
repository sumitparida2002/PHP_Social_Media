<?php

try {
    $dbConnection = parse_ini_file("db_connection.ini");
    extract($dbConnection);
    $myPdo = new PDO($dsn, $user, $password);
    $myPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
include("./common/header.php");
if (!isset($_SESSION["id"])) {
    header('Location: Welcome.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitBtn'])) {





    $title = $_POST["title"];
    $desc = $_POST['desc'];
    $userId = $_SESSION["id"];
    $access = $_POST["accessibility"];

    var_dump($title, $desc, $userId, $access);

    $insertStatement = $myPdo->prepare("INSERT INTO album (Title, Description,Owner_Id, Accessibility_Code) VALUES (:Title, :Desc, :OID, :ACode)");

    $insertStatement->execute([':Title' => $title, ':Desc' => $desc, ':OID' => $userId, ':ACode' => $access]);
}

session_start();

?>
<div class="container">
    <h1>Create New Album Selection</h1>
    <p>Welcome <?php echo $_SESSION["name"] ?>! (not you? change user <a href="Logout.php">here</a>), the following are your current registrations</p>

    <form action="MyAlbums.php" method="post">
        <table class="table">
            <tr>
                <th>Title</th>
                <th>Number of Pictures</th>
                <th>Accessibility</th>
                <th></th>
            </tr>

            <?php
            $sql = "SELECT a.Title AS Album_Title, a.Accessibility_Code, COUNT(p.Picture_Id) AS Picture_Count FROM album a LEFT JOIN picture p ON a.Album_Id = p.Album_Id WHERE
            a.Owner_Id = :OID GROUP BY a.Album_Id";

            $stmt = $myPdo->prepare($sql);

            $stmt->execute([':OID' => $_SESSION['id']]);
            var_dump($stmt->fetch(PDO::FETCH_ASSOC));

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";

                echo "<td>" . $row['Album_Title'] . "</td>";
                echo "<td>" . $row['Picture_Count'] . "</td>";
                echo "<td>" . $row['Accessibility_Code'] . "</td>";
                echo "<td>delete</td>";

                echo "</tr>";
            }
            ?>


        </table>





        <input type="submit" class="btn btn-primary" name="submitBtn" id="submitBtn" value="Save Changes">




    </form>
</div>
<?php include('./common/footer.php'); ?>