<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';


$oid = $_GET['oid'];

$query = "DELETE FROM tbl_overall WHERE oid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $oid);

if ($stmt->execute()) {
    header("Location: overallindex.php");
} else {
    echo "Error deleting record: " . $conn->error;
}

$stmt->close();
$conn->close();
