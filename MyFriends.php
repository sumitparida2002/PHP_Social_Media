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





    if (isset($_POST['defriend']) && is_array($_POST['defriend'])) {
        $defriendList = $_POST['defriend'];

        foreach ($defriendList as $friendId) {
            var_dump($friendId);
            $defriendStatement = $myPdo->prepare("DELETE FROM friendship WHERE (Friend_RequesterId = :userId AND Friend_RequesteeId = :friendId AND Status='accepted') OR (Friend_RequesterId = :friendId AND Friend_RequesteeId = :userId AND Status='accepted')");
            $defriendStatement->execute([':userId' => $_SESSION['id'], ':friendId' => $friendId]);
        }


        header("Location: MyFriends.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept'])) {


    if (isset($_POST['requests']) && is_array($_POST['requests'])) {
        $acceptList = $_POST['requests'];

        foreach ($acceptList as $requesterId) {


            $acceptStatement = $myPdo->prepare("UPDATE friendship SET Status = 'accepted' WHERE Friend_RequesterId = :requesterId AND Friend_RequesteeId = :userId");
            $acceptStatement->execute([':userId' => $_SESSION['id'], ':requesterId' => $requesterId]);
        }


        header("Location: MyFriends.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deny'])) {




    if (isset($_POST['requests']) && is_array($_POST['requests'])) {
        $denyList = $_POST['requests'];

        foreach ($denyList as $requesterId) {


            $denyStatement = $myPdo->prepare("DELETE FROM friendship WHERE Friend_RequesterId = :requesterId AND Friend_RequesteeId = :userId AND status='request'");
            $denyStatement->execute([':userId' => $_SESSION['id'], ':requesterId' => $requesterId]);
        }
    }


    header("Location: MyFriends.php");
    exit();
}

session_start();

?>
<div class="container">
    <h1>My Friends</h1>
    <p>Welcome <?php echo $_SESSION["name"] ?>! (not you? change user <a href="Logout.php">here</a>), the following are your current registrations</p>

    <form action="MyFriends.php" method="post">
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Shared Albums </th>
                <th>Defriend</th>
                <th></th>
            </tr>

            <?php

            $sql = "SELECT
            U.UserId AS FriendId,
            U.Name AS FriendName,
            COUNT(DISTINCT A.Album_Id) AS SharedAlbumsCount
        FROM
            user U
        JOIN friendship F ON (U.UserId = F.Friend_RequesterId OR U.UserId = F.Friend_RequesteeId)
        LEFT JOIN album A ON U.UserId = A.Owner_Id AND A.Accessibility_Code = 'shared'
        WHERE
            (:userId = F.Friend_RequesterId OR :userId = F.Friend_RequesteeId)
            AND F.Status = 'accepted'
            AND U.UserId != :userId 
        GROUP BY
            FriendId";

            $stmt = $myPdo->prepare($sql);

            $stmt->execute([':userId' => $_SESSION['id']]);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";

                echo "<td>" . $row['FriendName'] . "</td>";
                echo "<td>" . $row['SharedAlbumsCount'] . "</td>";
                echo "<td><input type='checkbox' name='defriend[]' value='" . $row['FriendId'] . "'></td>";

                echo "</tr>";
            }
            ?>


        </table>





        <input type="submit" class="btn btn-primary" onclick="confirmAction('defriend')" name="submitBtn" id="submitBtn" value="Defriend Selected">




    </form>

    <form action="MyFriends.php" method="post">
        <table class="table">
            <tr>
                <th>Name</th>
                <th>Accept or Deny </th>

            </tr>
            <?php
            $requestSql = "SELECT
            U.UserId AS RequesterId,
            U.Name AS RequesterName
            FROM
            user U
            JOIN friendship F ON U.UserId = F.Friend_RequesterId
            WHERE
            :userId = F.Friend_RequesteeId
            AND F.Status = 'request'";

            $requestStmt = $myPdo->prepare($requestSql);
            $requestStmt->execute([':userId' => $_SESSION['id']]);

            while ($requestRow = $requestStmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . $requestRow['RequesterName'] . "</td>";
                echo "<td><input type='checkbox' name='requests[]' value='" . $requestRow['RequesterId'] . "'></td> ";

                echo "</tr>";
            }
            ?>
        </table>
        <input type="submit" class="btn btn-primary" name="accept" onclick="confirmAction('accept')" id="accept" value="Accept Selected">
        <input type="submit" class="btn btn-primary" onclick="confirmAction('deny')" name="deny" id="deny" value="Deny Selected">

    </form>
</div>

<script>
    function confirmAction(action) {
        var confirmation = confirm("Are you sure you want to " + action + "?");

        if (confirmation) {
            // Set the selectedFriends input value and submit the form
            document.getElementById('selectedFriends').value = getSelectedFriends();
            document.getElementById('friendActionsForm').submit();
        }
    }

    function getSelectedFriends() {
        var selectedFriends = [];
        var checkboxes = document.getElementsByName('defriend[]');

        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                selectedFriends.push(checkbox.value);
            }
        });

        return selectedFriends.join(',');
    }
</script>
<?php include('./common/footer.php'); ?>