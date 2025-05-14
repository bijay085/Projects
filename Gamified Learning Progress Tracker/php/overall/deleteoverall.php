<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';


// Check if oid is provided
if (isset($_GET['oid'])) {
    $oid = $_GET['oid'];

    // Delete the record with the given oid
    $query = "DELETE FROM tbl_overall WHERE oid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $oid);

    if ($stmt->execute()) {
        echo "Record deleted successfully!";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "No oid provided!";
}
?>
