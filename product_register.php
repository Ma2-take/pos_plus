<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = intval($_POST['price'] ?? 0);
    $imagePath = null;

    if (!empty($_FILES['image']['tmp_name'])) {
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = 'uploads/' . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
        }
    }

    if ($name && $category && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $imagePath]);
        header("Location: products.php");
        exit;
    } else {
        $error = "全ての項目を正しく入力してください。";
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>商品登録</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">商品登録</h1>

    <?php if (!empty($error)): ?>
      <div class="mb-4 text-red-600 font-semibold"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4">
      <div>
        <label class="block font-semibold">商品名</label>
        <input type="text" name="name" required class="w-full border rounded px-2 py-1">
      </div>

      <div>
        <label class="block font-semibold">カテゴリ</label>
        <select name="category" required class="w-full border rounded px-2 py-1">
          <option value="">選択してください</option>
          <?php foreach (['飲料', '食品', '雑貨', 'その他'] as $cat): ?>
            <option value="<?= $cat ?>"><?= $cat ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold">価格（円）</label>
        <input type="number" name="price" min="1" required class="w-full border rounded px-2 py-1">
      </div>

      <div>
        <label class="block font-semibold">商品画像</label>
        <input type="file" name="image" accept="image/*" class="w-full">
      </div>

      <div class="flex gap-4 mt-2">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">登録</button>
        <a href="products.php" class="text-blue-500 hover:underline self-center">← 商品一覧に戻る</a>
      </div>
    </form>
  </div>
</body>
</html>
