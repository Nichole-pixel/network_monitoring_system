<?php
session_start();
include('../db.php');

// Get the client_id from the URL
if (isset($_GET['id'])) {
    $client_id = $_GET['id'];

    // Start a transaction to ensure both deletions happen together
    $conn->begin_transaction();

    try {
        // Delete records in the usage_log table that reference this client
        $stmt1 = $conn->prepare("DELETE FROM usage_log WHERE client_id = ?");
        $stmt1->bind_param("i", $client_id);
        $stmt1->execute();

        // Delete the client from the client table
        $stmt2 = $conn->prepare("DELETE FROM client WHERE client_id = ?");
        $stmt2->bind_param("i", $client_id);
        $stmt2->execute();

        // Commit the transaction if both deletes are successful
        $conn->commit();

        // Redirect to client dashboard with success message
        header("Location: client_dashboard.php?message=Client deleted successfully");
        exit();
    } catch (Exception $e) {
        // If something goes wrong, rollback the transaction
        $conn->rollback();
        header("Location: client_dashboard.php?message=Error deleting client: " . $e->getMessage());
        exit();
    }
} else {
    // If no client_id is provided, redirect with an error message
    header("Location: client_dashboard.php?message=Client ID missing");
    exit();
}
?>