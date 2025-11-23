<?php
require_once 'config.php';

$pdo = getDBConnection();

echo "=== BOOST STATUS CHECK ===\n";

try {
    // Check your recent rooms
    $stmt = $pdo->prepare('SELECT id, address, is_boosted, boost_expires_at FROM rooms WHERE user_id = 2 ORDER BY id DESC LIMIT 3');
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Your recent rooms:\n";
    foreach($rooms as $room) {
        echo "Room " . $room['id'] . ": " . $room['address'] . " - Boosted: " . ($room['is_boosted'] ? 'YES (until ' . $room['boost_expires_at'] . ')' : 'NO') . "\n";
    }

    echo "\nActive boosts:\n";
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM boosts WHERE status = "active" AND expires_at > NOW()');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total active boosts: " . $result['count'] . " (max 6 per city)\n";

    // Show recent payments
    echo "\nRecent payments:\n";
    $stmt = $pdo->prepare('SELECT id, amount, payment_type, status, created_at FROM payments WHERE user_id = 2 ORDER BY created_at DESC LIMIT 3');
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($payments as $payment) {
        echo "\${$payment['amount']} {$payment['payment_type']} - {$payment['status']} on " . date('M j', strtotime($payment['created_at'])) . "\n";
    }

    echo "\nNext: Run the correct Stripe CLI command and test a boost payment!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
