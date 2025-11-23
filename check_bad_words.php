<?php
require_once 'config.php';

function checkMessageForBadWords($message) {
    if (empty($message)) {
        return ['clean' => true, 'censored' => '', 'violations' => []];
    }

    try {
        $pdo = getDBConnection();

        // Get active bad words
        $stmt = $pdo->query("SELECT word, severity FROM bad_words WHERE is_active = TRUE ORDER BY severity DESC");
        $bad_words = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $violations = [];
        $censored_message = $message;

        foreach ($bad_words as $bad_word) {
            $word = $bad_word['word'];
            $severity = $bad_word['severity'];

            // Case-insensitive search
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';

            if (preg_match($pattern, $message)) {
                $violations[] = [
                    'word' => $word,
                    'severity' => $severity
                ];

                // Censor the word
                $censored_message = preg_replace($pattern, str_repeat('*', strlen($word)), $censored_message);
            }
        }

        $has_high_severity = array_filter($violations, function($v) {
            return $v['severity'] === 'high';
        });

        return [
            'clean' => count($violations) === 0,
            'censored' => $censored_message,
            'violations' => $violations,
            'blocked' => count($has_high_severity) > 0
        ];

    } catch (Exception $e) {
        error_log("Bad word check error: " . $e->getMessage());
        // On error, allow the message through
        return ['clean' => true, 'censored' => $message, 'violations' => []];
    }
}

function getBadWordsList() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT word, severity FROM bad_words WHERE is_active = TRUE");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Get bad words error: " . $e->getMessage());
        return [];
    }
}
?>
