<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // For local coursework/demo only.
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/db.php';

$tables = [
    'CUSTOMER' => [
        'pk' => 'CustomerID',
        'columns' => ['CustomerID','Name','Phone','Address'],
        'required' => ['CustomerID','Name','Phone','Address'],
        'numeric' => ['CustomerID']
    ],
    'STORE' => [
        'pk' => 'StoreID',
        'columns' => ['StoreID','StoreName','Location','Phone'],
        'required' => ['StoreID','StoreName','Location','Phone'],
        'numeric' => ['StoreID']
    ],
    'PRODUCT' => [
        'pk' => 'ProductID',
        'columns' => ['ProductID','ProductName','Category','Price'],
        'required' => ['ProductID','ProductName','Category','Price'],
        'numeric' => ['ProductID','Price']
    ],
    'ORDERS' => [
        'pk' => 'OrderID',
        'columns' => ['OrderID','CustomerID','OrderDate','TotalAmount','PaymentMode'],
        'required' => ['OrderID','CustomerID','OrderDate','TotalAmount','PaymentMode'],
        'numeric' => ['OrderID','CustomerID','TotalAmount']
    ],
    'ORDER_ITEM' => [
        'pk' => 'OrderItemID',
        'columns' => ['OrderItemID','OrderID','ProductID','Quantity','UnitPrice','Discount'],
        'required' => ['OrderItemID','OrderID','ProductID','Quantity','UnitPrice','Discount'],
        'numeric' => ['OrderItemID','OrderID','ProductID','Quantity','UnitPrice','Discount']
    ]
];

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getInput() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

$table = strtoupper($_GET['table'] ?? '');
if (!isset($tables[$table])) {
    respond(['error' => 'Invalid table. Use CUSTOMER, STORE, PRODUCT, ORDERS, or ORDER_ITEM.'], 400);
}

$cfg = $tables[$table];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT * FROM `' . $table . '` ORDER BY `' . $cfg['pk'] . '`');
        respond($stmt->fetchAll());
    }

    if ($method === 'POST') {
        $data = getInput();
        $clean = [];
        foreach ($cfg['columns'] as $col) {
            if (array_key_exists($col, $data)) {
                $clean[$col] = $data[$col];
            }
        }

        foreach ($cfg['required'] as $col) {
            if (!isset($clean[$col]) || $clean[$col] === '') {
                respond(['error' => "Missing required field: $col"], 422);
            }
        }

        foreach ($cfg['numeric'] as $col) {
            if (isset($clean[$col]) && !is_numeric($clean[$col])) {
                respond(['error' => "$col must be numeric."], 422);
            }
        }

        if ($table === 'PRODUCT' && (float)$clean['Price'] <= 0) {
            respond(['error' => 'Price must be greater than zero.'], 422);
        }
        if ($table === 'ORDERS') {
            if ((float)$clean['TotalAmount'] < 0) respond(['error' => 'TotalAmount cannot be negative.'], 422);
            if (!in_array($clean['PaymentMode'], ['Cash','Card','UPI'], true)) respond(['error' => 'PaymentMode must be Cash, Card, or UPI.'], 422);
        }
        if ($table === 'ORDER_ITEM') {
            if ((int)$clean['Quantity'] <= 0) respond(['error' => 'Quantity must be greater than zero.'], 422);
            if ((float)$clean['UnitPrice'] <= 0) respond(['error' => 'UnitPrice must be greater than zero.'], 422);
            if ((float)$clean['Discount'] < 0) respond(['error' => 'Discount cannot be negative.'], 422);
        }

        $columns = array_keys($clean);
        $quoted = array_map(fn($c) => '`' . $c . '`', $columns);
        $placeholders = array_map(fn($c) => ':' . $c, $columns);
        $sql = 'INSERT INTO `' . $table . '` (' . implode(',', $quoted) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);
        foreach ($clean as $col => $value) $stmt->bindValue(':' . $col, $value);
        $stmt->execute();

        respond(['message' => 'Record added successfully.', 'id' => $clean[$cfg['pk']] ?? null], 201);
    }

    if ($method === 'PUT') {
        $data = getInput();
        $id = $_GET['id'] ?? ($data[$cfg['pk']] ?? null);
        if ($id === null || $id === '') respond(['error' => 'Provide the record ID in ?id= or the request body.'], 400);

        $updates = [];
        foreach ($cfg['columns'] as $col) {
            if ($col !== $cfg['pk'] && array_key_exists($col, $data)) $updates[$col] = $data[$col];
        }
        if (!$updates) respond(['error' => 'No editable fields supplied.'], 422);

        $sets = [];
        foreach (array_keys($updates) as $col) $sets[] = '`' . $col . '` = :' . $col;
        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) .
               ' WHERE `' . $cfg['pk'] . '` = :record_id';
        $stmt = $pdo->prepare($sql);
        foreach ($updates as $col => $value) $stmt->bindValue(':' . $col, $value);
        $stmt->bindValue(':record_id', $id);
        $stmt->execute();

        respond(['message' => $stmt->rowCount() ? 'Record updated successfully.' : 'No record changed. Check the ID or values.']);
    }

    if ($method === 'DELETE') {
        $id = $_GET['id'] ?? '';
        if ($id === '') respond(['error' => 'Provide the record ID, for example ?table=CUSTOMER&id=5'], 400);

        $stmt = $pdo->prepare('DELETE FROM `' . $table . '` WHERE `' . $cfg['pk'] . '` = ?');
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) respond(['error' => 'Record not found.'], 404);
        respond(['message' => 'Record deleted successfully.']);
    }

    respond(['error' => 'Method not supported.'], 405);

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        respond(['error' => 'Constraint error: duplicate primary/unique value or a related record prevents this operation. Check foreign keys and unique phone numbers.'], 409);
    }
    respond(['error' => 'Database operation failed. Check the submitted values and table constraints.'], 500);
}
