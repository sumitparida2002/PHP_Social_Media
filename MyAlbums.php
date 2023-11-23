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




    $userId = $_SESSION["id"];

    foreach ($_POST['accessibility'] as $albumId => $accessibility) {
        $updateAccessibilityStatement = $myPdo->prepare("UPDATE album SET Accessibility_Code = :accessibility WHERE Album_Id = :albumId AND Owner_Id = :userId");
        $updateAccessibilityStatement->execute([':accessibility' => $accessibility, ':albumId' => $albumId, ':userId' => $userId]);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteBtn'])) {
    $userId = $_SESSION["id"];
    try {
        $albumIdToDelete = $_POST['deleteBtn'];

        $deletePicturesStatement = $myPdo->prepare("DELETE FROM picture WHERE Album_Id = :albumId");
        $deletePicturesStatement->execute([':albumId' => $albumIdToDelete]);

        $deleteStatement = $myPdo->prepare("DELETE FROM album WHERE Album_Id = :albumId AND Owner_Id = :userId");
        $deleteStatement->execute([':albumId' => $albumIdToDelete, ':userId' => $userId]);
    } catch (PDOException $e) {
        echo "Error deleting album: " . $e->getMessage();
    }
}

session_start();

?>
<div class="container">
    <h1>Create New Album Selection</h1>
    <p>Welcome <?php echo $_SESSION["name"] ?>! (not you? change user <a href="Logout.php">here</a>), the following are your current registrations</p>

    <form action="MyAlbums.php" id="myForm" method="post">
        <table class="table">
            <tr>
                <th>Title</th>
                <th>Number of Pictures</th>
                <th>Accessibility</th>
                <th></th>
            </tr>

            <?php
            $sql = "SELECT a.Album_Id as Album_Id, a.Title AS Album_Title, a.Accessibility_Code, COUNT(p.Picture_Id) AS Picture_Count FROM album a LEFT JOIN picture p ON a.Album_Id = p.Album_Id WHERE
            a.Owner_Id = :OID GROUP BY a.Album_Id";

            $stmt = $myPdo->prepare($sql);

            $stmt->execute([':OID' => $_SESSION['id']]);


            $sqlAccessibility = "SELECT Accessibility_Code, Description FROM accessibility";
            $stmtAccessibility = $myPdo->prepare($sqlAccessibility);
            $stmtAccessibility->execute();
            $accessibilityOptions = $stmtAccessibility->fetchAll(PDO::FETCH_ASSOC);


            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";

                echo "<td>" . $row['Album_Title'] . "</td>";
                echo "<td>" . $row['Picture_Count'] . "</td>";
                echo "<td>";
                echo "<select class='form-control' name='accessibility[{$row['Album_Id']}]''>";

                foreach ($accessibilityOptions as $option) {
                    $selected = ($row['Accessibility_Code'] == $option['Accessibility_Code']) ? 'selected' : '';
                    echo "<option value='{$option['Accessibility_Code']}' {$selected}>{$option['Description']}</option>";
                }

                echo "</select>";
                echo "</td>";
                echo "<td>";
                echo "<button type='button' class='btn btn-danger' onclick='confirmDelete({$row['Album_Id']})'>Delete</button>";

                echo "</td>";

                echo "</tr>";
            }
            ?>


        </table>





        <input type="submit" class="btn btn-primary" name="submitBtn" id="submitBtn" value="Save Changes">
        <input type="hidden" name="deleteBtn" id="deleteBtn" value="">





    </form>
</div>
<script>
    function confirmDelete(albumId) {
        var confirmDelete = confirm('Are you sure you want to delete this album?');
        if (confirmDelete) {
            document.getElementById('deleteBtn').value = albumId;
            document.getElementById('myForm').submit();
        }
    }
</script>
<?php include('./common/footer.php'); ?>