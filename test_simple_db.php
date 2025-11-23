<?php
require 'config.php';

try {
    $pdo = getDBConnection();

    // Test if payments table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'payments'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "✅ Payments table exists\n";

        // Check if there are any payments
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM payments");
        $result = $stmt->fetch();
        echo "📊 Total payments in database: " . $result['count'] . "\n";

        if ($result['count'] > 0) {
            // Show recent payments
            $stmt = $pdo->query("SELECT * FROM payments ORDER BY created_at DESC LIMIT 5");
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "\n📝 Recent payments:\n";
            foreach ($payments as $payment) {
                echo "- $" . number_format($payment['amount'], 2) . " ({$payment['status']}) - " . $payment['created_at'] . "\n";
            }
        } else {
            echo "❌ No payment records found in database\n";
        }
    } else {
        echo "❌ Payments table does not exist\n";
    }

} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
