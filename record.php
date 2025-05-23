<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items'])) {
    $itemsJson = $_POST['items'];
    $items = json_decode($itemsJson, true);

    if (!is_array($items) || count($items) === 0) {
        die("不正な商品データです。");
    }

    try {
        $pdo->beginTransaction();

        // 合計金額を計算
        $totalAmount = 0;
        foreach ($items as $item) {
            $quantity = intval($item['quantity']);
            $price = intval($item['price']);
            $amount = $quantity * $price;
            $totalAmount += $amount;
        }

        // sales テーブルに合計金額を保存
        $stmtSale = $pdo->prepare("INSERT INTO sales (amount) VALUES (?)");
        $stmtSale->execute([$totalAmount]);
        $saleId = $pdo->lastInsertId();

        // sale_items に各商品登録
        $stmtItem = $pdo->prepare("
            INSERT INTO sale_items (sale_id, product_id, quantity, price, amount)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $productId = ($item['id'] == 0) ? null : intval($item['id']); // 自由金額は null
            $quantity = intval($item['quantity']);
            $price = intval($item['price']);
            $amount = $quantity * $price;

            $stmtItem->execute([
                $saleId,
                $productId,
                $quantity,
                $price,
                $amount
            ]);
        }

        $pdo->commit();
        header("Location: history.php");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("登録に失敗しました: " . $e->getMessage());
    }
} else {
    echo "不正なアクセスです。";
}
