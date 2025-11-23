<?php
require_once 'config.php';

echo "Starting cleanup of expired files and messages...\n";

try {
    $pdo = getDBConnection();

    // Get current timestamp
    $now = date('Y-m-d H:i:s');
    echo "Current time: $now\n";

    // Delete expired file attachments
    $stmt = $pdo->prepare("
        SELECT id, file_path, original_name
        FROM file_attachments
        WHERE expires_at < ? AND expires_at IS NOT NULL
    ");
    $stmt->execute([$now]);
    $expiredFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filesDeleted = 0;
    $filesRemoved = 0;

    foreach ($expiredFiles as $file) {
        // Delete physical file if it exists
        if (file_exists($file['file_path'])) {
            if (unlink($file['file_path'])) {
                $filesRemoved++;
                echo "Removed file: {$file['original_name']}\n";
            } else {
                echo "Failed to remove file: {$file['original_name']}\n";
            }
        }

        // Delete database record
        $deleteStmt = $pdo->prepare("DELETE FROM file_attachments WHERE id = ?");
        $deleteStmt->execute([$file['id']]);
        $filesDeleted++;
    }

    echo "Deleted $filesDeleted expired file records, removed $filesRemoved physical files\n";

    // Delete expired DM messages
    $stmt = $pdo->prepare("
        SELECT id, message, file_name
        FROM dm_messages
        WHERE expires_at < ? AND expires_at IS NOT NULL
    ");
    $stmt->execute([$now]);
    $expiredDMMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dmMessagesDeleted = 0;
    foreach ($expiredDMMessages as $msg) {
        $deleteStmt = $pdo->prepare("DELETE FROM dm_messages WHERE id = ?");
        $deleteStmt->execute([$msg['id']]);
        $dmMessagesDeleted++;

        $preview = $msg['file_name'] ? "File: {$msg['file_name']}" : substr($msg['message'] ?: '', 0, 50);
        echo "Deleted expired DM message: $preview\n";
    }

    echo "Deleted $dmMessagesDeleted expired DM messages\n";

    // Delete expired public chat messages
    $stmt = $pdo->prepare("
        SELECT id, message
        FROM chat_messages
        WHERE expires_at < ? AND expires_at IS NOT NULL
    ");
    $stmt->execute([$now]);
    $expiredPublicMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $publicMessagesDeleted = 0;
    foreach ($expiredPublicMessages as $msg) {
        $deleteStmt = $pdo->prepare("DELETE FROM chat_messages WHERE id = ?");
        $deleteStmt->execute([$msg['id']]);
        $publicMessagesDeleted++;

        $preview = substr($msg['message'] ?: '', 0, 50);
        echo "Deleted expired public message: $preview\n";
    }

    echo "Deleted $publicMessagesDeleted expired public messages\n";

    // Clean up orphaned file attachments (files not linked to any message)
    $stmt = $pdo->prepare("
        SELECT fa.id, fa.file_path, fa.original_name
        FROM file_attachments fa
        LEFT JOIN chat_messages cm ON fa.message_id = cm.id
        LEFT JOIN dm_messages dm ON fa.dm_message_id = dm.id
        WHERE fa.message_id IS NOT NULL AND cm.id IS NULL
           OR fa.dm_message_id IS NOT NULL AND dm.id IS NULL
    ");
    $stmt->execute();
    $orphanedFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orphanedDeleted = 0;
    $orphanedRemoved = 0;

    foreach ($orphanedFiles as $file) {
        // Delete physical file if it exists
        if (file_exists($file['file_path'])) {
            if (unlink($file['file_path'])) {
                $orphanedRemoved++;
            }
        }

        // Delete database record
        $deleteStmt = $pdo->prepare("DELETE FROM file_attachments WHERE id = ?");
        $deleteStmt->execute([$file['id']]);
        $orphanedDeleted++;
    }

    echo "Cleaned up $orphanedDeleted orphaned file records, removed $orphanedRemoved physical files\n";

    // Update DM thread last_message_at when messages are deleted
    $pdo->exec("
        UPDATE dm_threads dt
        SET last_message_at = (
            SELECT MAX(created_at)
            FROM dm_messages dm
            WHERE dm.thread_id = dt.thread_id
        )
        WHERE EXISTS (
            SELECT 1 FROM dm_messages dm
            WHERE dm.thread_id = dt.thread_id
        )
    ");

    echo "Updated DM thread timestamps\n";

    // Deactivate empty DM threads (no active messages)
    $pdo->exec("
        UPDATE dm_threads
        SET is_active = FALSE
        WHERE thread_id NOT IN (
            SELECT DISTINCT thread_id FROM dm_messages
        )
    ");

    echo "Deactivated empty DM threads\n";

    echo "Cleanup completed successfully!\n";

} catch (Exception $e) {
    echo "Cleanup failed: " . $e->getMessage() . "\n";
    error_log("Cleanup error: " . $e->getMessage());
    exit(1);
}
?>
