<?php
// データベース接続テストスクリプト

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== データベース接続テスト ===\n\n";

// .envから設定を読み込み
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
$database = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE');
$username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME');
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');

echo "DB_HOST: $host\n";
echo "DB_DATABASE: $database\n";
echo "DB_USERNAME: $username\n";
echo "\n";

try {
    // LaravelのDB接続をテスト
    $pdo = DB::connection()->getPdo();
    echo "✓ Laravel接続成功\n";
    echo "接続先: " . DB::connection()->getDatabaseName() . "\n";
    
    // テーブル一覧を取得
    $tables = DB::select('SHOW TABLES');
    echo "\n既存のテーブル数: " . count($tables) . "\n";
    
} catch (Exception $e) {
    echo "✗ Laravel接続失敗\n";
    echo "エラー: " . $e->getMessage() . "\n";
    echo "\n";
    
    // 直接PDOで接続を試す
    echo "=== 直接PDO接続テスト ===\n";
    try {
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✓ 直接PDO接続成功\n";
    } catch (PDOException $e) {
        echo "✗ 直接PDO接続失敗\n";
        echo "エラー: " . $e->getMessage() . "\n";
    }
}

