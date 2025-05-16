<?php
require 'db.php';

// 登録・更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = intval($_POST['price'] ?? 0);
    $imagePath = $_POST['current_image'] ?? null;

    if (!empty($_FILES['image']['tmp_name'])) {
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = 'uploads/' . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
        }
    }

    if ($name && $category && $price > 0) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, price=?, image_path=? WHERE id=?");
            $stmt->execute([$name, $category, $price, $imagePath, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, image_path) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $category, $price, $imagePath]);
        }
        header("Location: products.php");
        exit;
    }
}

// 削除処理
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$deleteId]);
    header("Location: products.php");
    exit;
}

// 編集モード
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
}

// 一覧
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>商品マスタ管理</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">商品マスタ管理</h1>

    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 mb-6">
      <input type="hidden" name="id" value="<?= htmlspecialchars($editProduct['id'] ?? '') ?>">
      <input type="hidden" name="current_image" value="<?= htmlspecialchars($editProduct['image_path'] ?? '') ?>">

      <div>
        <label class="block font-semibold">商品名</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>"
               class="w-full border rounded px-2 py-1">
      </div>

      <div>
        <label class="block font-semibold">カテゴリ</label>
        <select name="category" required class="w-full border rounded px-2 py-1">
          <option value="">選択してください</option>
          <?php
          $categories = ['飲料', '食品', '雑貨', 'その他'];
          foreach ($categories as $cat) {
              $selected = ($editProduct && $editProduct['category'] === $cat) ? 'selected' : '';
              echo "<option value='$cat' $selected>$cat</option>";
          }
          ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold">金額（円）</label>
        <input type="number" name="price" min="1" required value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>"
               class="w-full border rounded px-2 py-1">
      </div>

      <div>
        <label class="block font-semibold">商品画像</label>
        <input type="file" name="image" accept="image/*" class="w-full">
        <?php if (!empty($editProduct['image_path'])): ?>
          <img src="<?= $editProduct['image_path'] ?>" alt="画像" class="w-24 mt-2 border">
        <?php endif; ?>
      </div>

      <div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          <?= $editProduct ? '更新' : '登録' ?>
        </button>
        <?php if ($editProduct): ?>
          <a href="products.php" class="ml-4 text-blue-500 hover:underline">キャンセル</a>
        <?php endif; ?>
      </div>
    </form>

    <table class="w-full text-sm border">
      <thead>
        <tr class="bg-gray-200">
          <th class="py-2 px-2">画像</th>
          <th class="py-2 px-2">商品名</th>
          <th class="py-2 px-2">カテゴリ</th>
          <th class="py-2 px-2 text-right">金額</th>
          <th class="py-2 px-2 text-center">操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr class="border-t">
            <td class="py-2 px-2">
              <?php if ($p['image_path']): ?>
                <img src="<?= $p['image_path'] ?>" alt="" class="w-16 border">
              <?php endif; ?>
            </td>
            <td class="py-2 px-2"><?= htmlspecialchars($p['name']) ?></td>
            <td class="py-2 px-2"><?= htmlspecialchars($p['category']) ?></td>
            <td class="py-2 px-2 text-right">¥<?= number_format($p['price']) ?></td>
            <td class="py-2 px-2 text-center">
              <a href="?edit=<?= $p['id'] ?>" class="text-blue-500 hover:underline mr-2">編集</a>
              <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('削除しますか？')" class="text-red-500 hover:underline">削除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="mt-4 text-center">
      <a href="index.php" class="text-blue-500 hover:underline">← レジに戻る</a>
    </div>
  </div>
</body>
</html>
