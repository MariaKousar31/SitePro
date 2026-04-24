<?php
$conn = mysqli_connect(
    "sql100.infinityfree.com",
    "if0_41688806",
    "UVMmk132@//TuvEwu7^jytdbwyt,!",
    "if0_41688806_db_carbon"
);

if (!$conn) {
    die("DB Connection Failed: " . mysqli_connect_error());
}
?>