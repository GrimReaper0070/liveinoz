<?php
// get_events.php - Fetch events with filtering

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/config.php';

try {
    // Test database connection first
    $pdo = getDBConnection();

    // Get filter parameters
    $city = $_GET['city'] ?? 'all';
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);

    // Build query
    $where = ["status = 'approved'"];
    $params = [];

    if ($city !== 'all') {
        $where[] = "city = ?";
        $params[] = $city;
    }

    $whereClause = implode(' AND ', $where);

    // Get events
    $query = "
        SELECT
            e.id,
            e.title,
            e.description,
            e.city,
            e.state_code,
            e.event_date,
            e.event_time,
            e.contact_method,
            e.contact_phone,
            e.contact_email,
            e.allow_chat,
            e.poster_path,
            e.created_at,
            e.view_count,
            u.first_name,
            u.last_name
        FROM events e
        LEFT JOIN users u ON e.user_id = u.id
        WHERE $whereClause
        ORDER BY e.event_date ASC, e.created_at DESC
        LIMIT $limit OFFSET $offset
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format events for frontend
    $formattedEvents = [];
    foreach ($events as $event) {
        $formattedEvents[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'description' => $event['description'],
            'city' => $event['city'],
            'state_code' => $event['state_code'],
            'event_date' => $event['event_date'],
            'event_time' => $event['event_time'],
            'contact_method' => $event['contact_method'],
            'contact_phone' => $event['contact_phone'],
            'contact_email' => $event['contact_email'],
            'allow_chat' => (bool)$event['allow_chat'],
            'poster_path' => $event['poster_path'],
            'created_at' => $event['created_at'],
            'view_count' => $event['view_count'],
            'organizer' => $event['first_name'] . ' ' . $event['last_name']
        ];
    }

    $response = [
        'success' => true,
        'events' => $formattedEvents,
        'total' => count($formattedEvents),
        'debug' => [
            'city_filter' => $city,
            'query_params' => $params,
            'events_found' => count($events)
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Get events error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load events',
        'error' => $e->getMessage(),
        'debug' => [
            'file' => __FILE__,
            'line' => __LINE__,
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?>
