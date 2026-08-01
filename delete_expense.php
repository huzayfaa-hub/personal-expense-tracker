<?php

$conn = mysqli_connect("localhost","root","","grocery_tracker");

if(isset($_GET['id']))
{
    $id = $_GET['id'];

    $sql = "DELETE FROM expenses WHERE id='$id'";
    mysqli_query($conn,$sql);

    header("Location: view_expenses.php");
    exit();
}

?>