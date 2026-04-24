<?php
$conn = mysqli_connect(
    "sql100.infinityfree.com",
    "if0_41688806",
    "mmmk131k31!@#$545",
    "if0_41688806_db_carbon"
);

if (!$conn) {
    die("DB Connection Failed: " . mysqli_connect_error());
}
?>