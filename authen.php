<?php 
session_start();

if (isset($_POST['m_username'])) {
    include("condb.php");

    // Clean input
    $m_username = mysqli_real_escape_string($condb, $_POST['m_username']);
    $m_password = mysqli_real_escape_string($condb, sha1($_POST['m_password']));

    // Use prepared statements for security
    $sql = "SELECT * FROM tbl_emp WHERE m_username = ? AND m_password = ?";
    $stmt = mysqli_prepare($condb, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $m_username, $m_password); // 'ss' for two string parameters
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);

        // Store session data
        $_SESSION["m_id"] = $row["m_id"];
        $_SESSION["m_level"] = $row["m_level"];

        // Redirect based on user level
        if ($_SESSION["m_level"] == "admin") {
            header("Location: admin.php");
            exit(); // Stop further script execution
        } elseif ($_SESSION["m_level"] == "staff") {
            header("Location: profile.php");
            exit(); // Stop further script execution
        } else {
            echo "<script>alert('Unknown user level!');</script>";
        }

    } else {
        echo "<script>";
        echo "alert('User or password is incorrect');"; 
        echo "window.history.back();";
        echo "</script>";
    }
} else {
    header("Location: index.php"); // If the form isn't submitted, go back to login page
    exit();
}
?>
