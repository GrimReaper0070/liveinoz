<?php
require_once 'config.php';

class RateLimiter {
    private $pdo;
    private $max_messages_per_minute = 30; // Increased for testing
    private $max_messages_per_hour = 200; // Increased for testing

    public function __construct() {
        $this->pdo = getDBConnection();
    }

    /**
     * Check if user can send a message
     * @param int $user_id User ID
     * @return array ['allowed' => bool, 'wait_time' => int seconds, 'message' => string]
     */
    public function canSendMessage($user_id) {
        try {
            // Clean old rate limit records (older than 1 hour)
            $this->cleanupOldRecords();

            // Count messages in the last minute
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM message_rate_limits
                WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
            ");
            $stmt->execute([$user_id]);
            $minute_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Count messages in the last hour
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as count FROM message_rate_limits
                WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$user_id]);
            $hour_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Check rate limits
            if ($minute_count >= $this->max_messages_per_minute) {
                $wait_time = 60 - $this->getSecondsSinceLastMessage($user_id);
                return [
                    'allowed' => false,
                    'wait_time' => max(1, $wait_time),
                    'message' => "Too many messages. Please wait {$wait_time} seconds."
                ];
            }

            if ($hour_count >= $this->max_messages_per_hour) {
                $wait_time = 3600 - $this->getSecondsSinceLastMessage($user_id, 3600);
                return [
                    'allowed' => false,
                    'wait_time' => max(1, $wait_time),
                    'message' => "Hourly message limit reached. Please wait " . ceil($wait_time / 60) . " minutes."
                ];
            }

            return ['allowed' => true, 'wait_time' => 0, 'message' => ''];

        } catch (Exception $e) {
            error_log("Rate limiter error: " . $e->getMessage());
            // On error, allow the message to prevent blocking legitimate users
            return ['allowed' => true, 'wait_time' => 0, 'message' => ''];
        }
    }

    /**
     * Record a message send for rate limiting
     * @param int $user_id User ID
     * @return bool Success
     */
    public function recordMessage($user_id) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO message_rate_limits (user_id, created_at)
                VALUES (?, NOW())
            ");
            return $stmt->execute([$user_id]);
        } catch (Exception $e) {
            error_log("Rate limiter record error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get seconds since user's last message
     * @param int $user_id User ID
     * @param int $seconds_back How far back to look (default 60)
     * @return int Seconds since last message
     */
    private function getSecondsSinceLastMessage($user_id, $seconds_back = 60) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_diff
                FROM message_rate_limits
                WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$user_id, $seconds_back]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['seconds_diff'] : $seconds_back;
        } catch (Exception $e) {
            return $seconds_back;
        }
    }

    /**
     * Clean up old rate limit records
     */
    private function cleanupOldRecords() {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM message_rate_limits
                WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute();
        } catch (Exception $e) {
            // Silently fail cleanup
        }
    }
}

// Helper function for easy use
function checkRateLimit($user_id) {
    $limiter = new RateLimiter();
    return $limiter->canSendMessage($user_id);
}

function recordMessageSend($user_id) {
    $limiter = new RateLimiter();
    return $limiter->recordMessage($user_id);
}
?>
