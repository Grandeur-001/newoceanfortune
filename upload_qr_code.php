<?php 

include 'connection.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $walletAddress = $_POST['wallet_address'] ?? '';
    $walletNetwork = $_POST['wallet_network'] ?? '';
    $qrCodeFile = $_FILES['qr_code'] ?? null;

    if (empty($walletAddress) || empty($walletNetwork) || empty($qrCodeFile)) {
        echo "All fields are required.";
        exit;
    }

    $targetDir = "qr_code/";
    $fileName = basename($qrCodeFile["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($qrCodeFile["tmp_name"], $targetFilePath)) {
        $stmt = $conn->prepare("INSERT INTO wallet_info (wallet_address, wallet_network, qr_code) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $walletAddress, $walletNetwork, $targetFilePath);

        if ($stmt->execute()) {
            echo "Wallet info and QR code uploaded successfully!";
        } else {
            echo "Database error: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Failed to upload QR code.";
    }
}

$conn->close();



?>