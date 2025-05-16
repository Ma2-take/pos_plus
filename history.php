<?php
require 'db.php';

// sales と sale_items、products を結合して取得
$stmt = $pdo->query(
  "SELECT s.id AS sale_id, s.recorded_at, 
          p.name AS product_name, p.category, 
          si.quantity, si.price, si.amount
   FROM sales s
   JOIN sale_items si ON s.id = si.sale_id
   JOIN products p ON p.id = si.product_id
   ORDER BY s.recorded_at DESC, s.id DESC"
);
$saleDetails = $stmt->fetchAll();

// sale_id ごとにグループ化
$sales = [];
foreach ($saleDetails as $row) {
    $sales[$row['sale_id']]['recorded_at'] = $row['recorded_at'];
    $sales[$row['sale_id']]['items'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>売上履歴</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-4">売上履歴</h1>

    <?php if (empty($sales)): ?>
      <p class="text-center text-gray-500">売上データがありません。</p>
    <?php else: ?>
      <?php foreach ($sales as $saleId => $sale): ?>
        <div class="mb-6 border-b pb-4">
          <div class="text-sm text-gray-500 mb-2">日時: <?= htmlspecialchars($sale['recorded_at']) ?></div>
          <table class="w-full text-sm border">
            <thead class="bg-gray-100">
              <tr>
                <th class="py-1 px-2 text-left">商品名</th>
                <th class="py-1 px-2 text-left">カテゴリ</th>
                <th class="py-1 px-2 text-right">単価</th>
                <th class="py-1 px-2 text-right">数量</th>
                <th class="py-1 px-2 text-right">小計</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $total = 0;
              foreach ($sale['items'] as $item): 
                $subtotal = $item['amount'];
                $total += $subtotal;
              ?>
                <tr class="border-t">
                  <td class="py-1 px-2"><?= htmlspecialchars($item['product_name']) ?></td>
                  <td class="py-1 px-2"><?= htmlspecialchars($item['category']) ?></td>
                  <td class="py-1 px-2 text-right">¥<?= number_format($item['price']) ?></td>
                  <td class="py-1 px-2 text-right"><?= $item['quantity'] ?></td>
                  <td class="py-1 px-2 text-right">¥<?= number_format($subtotal) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="bg-gray-100 font-bold">
                <td colspan="4" class="py-1 px-2 text-right">合計</td>
                <td class="py-1 px-2 text-right">¥<?= number_format($total) ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <div class="text-center mt-6">
      <a href="index.php" class="text-blue-500 hover:underline">← 金額入力に戻る</a>
    </div>
  </div>
</body>
</html>
