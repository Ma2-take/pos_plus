<?php
require 'db.php';
$stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>複数商品レジ</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">商品選択レジ</h1>

    <!-- 商品ボタン一覧 -->
    <div class="grid grid-cols-4 gap-3 mb-6">
      <?php foreach ($products as $p): ?>
        <button onclick='selectProduct(<?= json_encode($p) ?>)'
                class="border rounded p-2 text-center bg-blue-100 hover:bg-blue-200">
          <?php if ($p['image_path']): ?>
            <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="" class="h-16 mx-auto mb-1">
          <?php endif; ?>
          <div class="text-sm font-bold"><?= htmlspecialchars($p['name']) ?></div>
          <div class="text-xs">￥<?= $p['price'] ?></div>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- 数字テンキー（商品数量用） -->
    <div class="grid grid-cols-3 gap-2 mb-4 w-1/2">
      <?php foreach ([7,8,9,4,5,6,1,2,3] as $num): ?>
        <button onclick="press(<?= $num ?>)" class="border rounded py-4 text-xl bg-white hover:bg-gray-200"><?= $num ?></button>
      <?php endforeach; ?>
      <button onclick="press(0)" class="col-span-2 border rounded py-4 text-xl bg-white hover:bg-gray-200">0</button>
      <button onclick="confirmQuantity()" class="border rounded py-4 text-xl bg-green-100 hover:bg-green-200">×</button>
    </div>

    <!-- アコーディオン自由金額入力エリア -->
    <div class="mb-4">
      <button type="button" onclick="toggleCustomAmount()" class="w-full text-left bg-gray-200 px-4 py-2 rounded-t font-semibold">
        ＋ 自由金額入力
      </button>
      <div id="customAmountPanel" class="hidden border-t border-gray-300 p-4 bg-gray-50">
        <div class="flex items-center space-x-2 mb-2">
          <div id="customAmountDisplay" class="text-2xl font-mono bg-gray-100 p-2 w-40 text-right">0</div>
          <button onclick="addCustomAmount()" class="bg-green-300 px-4 py-2 rounded">金額追加</button>
        </div>
        <div class="grid grid-cols-3 gap-2 w-48 mb-4">
          <?php foreach ([7,8,9,4,5,6,1,2,3] as $num): ?>
            <button onclick="customPress(<?= $num ?>)" class="border py-2 rounded bg-white hover:bg-gray-100 text-xl"><?= $num ?></button>
          <?php endforeach; ?>
          <button onclick="customClear()" class="border py-2 rounded bg-red-100 text-xl">C</button>
          <button onclick="customPress(0)" class="col-span-2 border py-2 rounded bg-white hover:bg-gray-100 text-xl">0</button>
        </div>
      </div>
    </div>

    <!-- 小計エリア -->
    <div class="mb-4">
      <h2 class="font-semibold mb-2">選択中の商品:</h2>
      <ul id="itemList" class="list-disc pl-5 text-sm"></ul>
      <div id="total" class="text-right font-bold text-lg mt-2">合計: ¥0</div>
    </div>

    <!-- 操作ボタン -->
    <form id="saleForm" action="record.php" method="POST">
      <input type="hidden" name="items" id="itemsInput">
      <div class="flex gap-4">
        <button type="button" onclick="addTax()" class="bg-yellow-200 px-4 py-2 rounded">税込み</button>
        <button type="button" onclick="clearAll()" class="bg-red-200 px-4 py-2 rounded">クリア</button>
        <button type="submit" class="bg-green-400 text-white px-4 py-2 rounded">計上</button>
        <a href="history.php" class="bg-blue-300 px-4 py-2 rounded">履歴</a>
      </div>
    </form>
  </div>

<script>
let currentQuantity = "";
let selectedProduct = null;
let items = [];
let customInput = "";

function press(num) {
  currentQuantity += num;
}

function selectProduct(product) {
  selectedProduct = product;
  currentQuantity = "";
  alert(product.name + ' を選択しました。数量を入力して×を押してください');
}

function confirmQuantity() {
  if (!selectedProduct || !currentQuantity) return alert('商品と数量を選択してください');
  const quantity = parseInt(currentQuantity);
  if (quantity <= 0) return;
  const item = {
    id: selectedProduct.id,
    name: selectedProduct.name,
    price: selectedProduct.price,
    quantity: quantity
  };
  items.push(item);
  updateList();
  currentQuantity = "";
  selectedProduct = null;
}

function updateList() {
  const list = document.getElementById("itemList");
  list.innerHTML = "";
  let total = 0;
  for (const item of items) {
    const li = document.createElement("li");
    const subtotal = item.price * item.quantity;
    li.textContent = `${item.name} ×${item.quantity} = ¥${subtotal}`;
    list.appendChild(li);
    total += subtotal;
  }
  document.getElementById("total").textContent = `合計: ¥${total}`;
  document.getElementById("itemsInput").value = JSON.stringify(items);
}

function addTax() {
  for (let item of items) {
    item.price = Math.round(item.price * 1.1);
  }
  updateList();
}

function clearAll() {
  items = [];
  currentQuantity = "";
  selectedProduct = null;
  customInput = "";
  document.getElementById("customAmountDisplay").innerText = "0";
  updateList();
}

function customPress(num) {
  customInput += num.toString();
  document.getElementById("customAmountDisplay").innerText = customInput;
}

function customClear() {
  customInput = "";
  document.getElementById("customAmountDisplay").innerText = "0";
}

function addCustomAmount() {
  if (!customInput || isNaN(customInput)) return alert("金額を入力してください");
  const amount = parseInt(customInput);
  if (amount <= 0) return;
  const item = {
    id: 0,
    name: "自由金額",
    price: amount,
    quantity: 1
  };
  items.push(item);
  updateList();
  customInput = "";
  document.getElementById("customAmountDisplay").innerText = "0";
}

function toggleCustomAmount() {
  const panel = document.getElementById("customAmountPanel");
  panel.classList.toggle("hidden");
}
</script>
</body>
</html>
