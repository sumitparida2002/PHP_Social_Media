<?php





$nameValue = isset($_POST["name"]) ? $_POST["name"] : "";
$UIDValue = isset($_POST["userID"]) ? $_POST["userID"] : "";
$phoneNumberValue = isset($_POST["phone"]) ? $_POST["phone"] : "";


function ValidateName($name)
{
    if (empty($name)) {
        return "Name is required.";
    }
    return "";
}

function ValidateUID($UID)
{
    if (empty($UID)) {
        return "User ID is required.";
    }
    return "";
}


function ValidatePassword($password)
{
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/';

    if (!preg_match($password_pattern, $password)) {
        return "Password is invalid.";
    } else {
        return "";
    }
}

function ValidatePhoneNumber($phoneNumber)
{
    $phone_number_pattern = '/^\d{3}-\d{3}-\d{4}$/';

    if (!preg_match($phone_number_pattern, $phoneNumber)) {
        return "Phone number is invalid.";
    } else {
        return "";
    }
}



$nameError = $userIDError = $phoneError = $passwordError = $confError = $alreadyExistError = "";
if (isset($_POST["submit"])) {

    $Cname = $_POST['name'];
    $UID = $_POST['userID'];
    $phoneNumber = $_POST['phone'];
    $Password = $_POST['password'];
    $confPassword = $_POST['confPassword'];









    $nameError = ValidateName($Cname);
    $userIDError = ValidateUID($UID);
    $phoneError = ValidatePhoneNumber($phoneNumber);
    $passwordError = ValidatePassword($Password);











    if (empty($nameError) && empty($userIDError) && empty($phoneError) && empty($passwordError)) {

        try {
            $dbConnection = parse_ini_file("db_connection.ini");
            extract($dbConnection);
            $myPdo = new PDO($dsn, $user, $password);

            $myPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            print "Not Connecting";
            die();
        }




        $sqlSelect = "SELECT * FROM user where UserId = :UID";
        $stmt = $myPdo->prepare($sqlSelect);
        $stmt->execute([':UID' => $UID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);




        if (!empty($row)) {
            $alreadyExistError = "A user with this ID has already signed up";
        } else {
            $hashedPassword = hash("sha256", $Password);
            $sqlInsert = "INSERT INTO user (UserId, Name, Phone, Password) VALUES (:UID, :Cname, :phoneNumber, :hashedPassword)";
            $stmt = $myPdo->prepare($sqlInsert);
            $stmt->execute([
                ':UID' => $UID,
                ':Cname' => $Cname,
                ':phoneNumber' => $phoneNumber,
                ':hashedPassword' => $hashedPassword,
            ]);
            header('Location: Welcome.php');
        }
    }
}

include("./common/Header.php");
?>
<div class="container">

    <h1>Sign Up</h1>
    <form method="post" action="NewUser.php" id="form">




        <label class="form-label" for="userID">User ID:</label>
        <input class="form-control" type="text" id="userID" name="userID" value="<?php echo $UIDValue; ?>">
        <p class='text-danger'><?php echo $userIDError; ?></p>
        <p class='text-danger'><?php echo $alreadyExistError; ?></p>


        <label class="form-label" for="name">Name:</label>
        <input class="form-control" type="text" id="name" name="name" value="<?php echo $nameValue; ?>">
        <p class='text-danger'><?php echo $nameError; ?></p>





        <label class="form-label" for="phone">Phone:</label>
        <input class="form-control" type="tel" id="phone" name="phone" value="<?php echo $phoneNumberValue; ?>"><br>
        <p class='text-danger'><?php echo $phoneError; ?></p>


        <label class="form-label" for="password">Password:</label>
        <input class="form-control" type="password" id="password" name="password" value=""><br>
        <p class='text-danger'><?php echo $passwordError; ?></p>


        <label class="form-label" for="confPassword">Password Again:</label>
        <input class="form-control" type="password" id="confPassword" name="confPassword" value=""><br>
        <p class='text-danger'><?php echo $confError; ?></p>









        <button type="submit" class="btn btn-primary" name="submit">Submit</button>


        <button class="btn btn-primary" id="clear" name="clear">Clear</button>


    </form>

    <script>
        const clearBtn = document.getElementById("clear");
        clearBtn.addEventListener("click", (e) => {

            document.getElementById("form").reset();
        });
    </script>
</div>
<?php include('./common/footer.php'); ?>