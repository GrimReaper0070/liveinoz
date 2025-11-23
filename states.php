<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try {
    $pdo = getDBConnection();

    // Get user language preference, or use query param if provided
    $lang = $_GET['lang'] ?? null;
    if (!$lang) {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT language FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $pref = $stmt->fetch(PDO::FETCH_ASSOC);
        $lang = $pref['language'] ?? 'en';
    }

    // Get all states with their main cities
    $stmt = $pdo->query("SELECT code, name, name_es, main_city FROM states ORDER BY name");
    $states = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response based on language
    $formatted_states = array_map(function($state) use ($lang) {
        return [
            'code' => $state['code'],
            'name' => $lang === 'es' ? $state['name_es'] : $state['name'],
            'main_city' => $state['main_city']
        ];
    }, $states);

    echo json_encode([
        'success' => true,
        'states' => $formatted_states,
        'language' => $lang
    ]);

} catch (Exception $e) {
    error_log("States API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load states']);
}
?>
