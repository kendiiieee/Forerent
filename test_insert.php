<?php
$conn = new PDO('pgsql:host=postgres;dbname=forerent;user=root;password=root');

// Get some existing request IDs
$stmt = $conn->query("SELECT request_id FROM maintenance_requests WHERE status = 'Completed' LIMIT 20");
$requestIds = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $requestIds[] = $row['request_id'];
}

if (count($requestIds) >= 5) {
    // Insert maintenance logs for May 2026
    $costs = [1000, 1500, 2000, 800, 500];

    for ($i = 0; $i < 5; $i++) {
        $id = $requestIds[$i];
        $cost = $costs[$i];
        $conn->exec("INSERT INTO maintenance_logs (request_id, completion_date, cost) VALUES ($id, '2026-05-11', $cost)");
    }

    // Verify
    $stmt = $conn->query("SELECT COUNT(*) FROM maintenance_logs WHERE EXTRACT(MONTH FROM completion_date) = 5 AND EXTRACT(YEAR FROM completion_date) = 2026");
    echo "Maintenance logs inserted for May 2026: " . $stmt->fetchColumn() . "\n";

    // Show breakdown
    $stmt = $conn->query("
        SELECT mr.category, SUM(ml.cost) as total
        FROM maintenance_logs ml
        JOIN maintenance_requests mr ON ml.request_id = mr.request_id
        WHERE EXTRACT(MONTH FROM ml.completion_date) = 5
        AND EXTRACT(YEAR FROM ml.completion_date) = 2026
        GROUP BY mr.category
    ");

    echo "\nMay 2026 breakdown:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  " . $row['category'] . ": ₱" . $row['total'] . "\n";
    }
}
