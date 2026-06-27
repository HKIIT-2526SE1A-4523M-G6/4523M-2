/* ============================================
   Order Page Logic - js/order.js
   ============================================ */

document.addEventListener("DOMContentLoaded", () => {
  // 从 localStorage 读取购物车数据
  const cartData = localStorage.getItem("cart")
  let cart = []
  if (cartData) {
    cart = JSON.parse(cartData)
  }

  const orderSection = document.getElementById("order")
  const formBox = orderSection.querySelector(".form-box")

  // 如果购物车为空，提示用户
  if (cart.length === 0) {
    formBox.innerHTML = `
      <h3 class="form-title">Order Details</h3>
      <p>Your cart is empty. Please go back to <a href="index.php">Home</a> and add items.</p>
    `
    return
  }

  // 渲染购物车商品列表
  const cartList = document.createElement("div")
  cartList.className = "cart-summary"

  cart.forEach(item => {
    const card = document.createElement("div")
    card.className = "card"
    card.innerHTML = `
      <div class="card-body">
        <h3 class="card-title">${item.name}</h3>
        <p class="card-desc">Model: ${item.model}</p>
        <div class="card-price">¥${item.price.toFixed(2)} × ${item.qty}</div>
      </div>
    `
    cartList.appendChild(card)
  })

  // 总价卡片
  const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0)
  const totalCard = document.createElement("div")
  totalCard.className = "card"
  totalCard.innerHTML = `
    <div class="card-body">
      <h3 class="card-title">Total</h3>
      <p class="card-desc">Summary of your cart</p>
      <div class="card-price">¥${total.toFixed(2)}</div>
    </div>
  `
  cartList.appendChild(totalCard)

  // 把购物车展示插入到表单前面
  formBox.insertBefore(cartList, formBox.firstChild)

  // 同时填充下拉框选项
  const orderSelect = formBox.querySelector("select[name='fid']")
  if (orderSelect) {
    // orderSelect.innerHTML = cart.map((item, index) => `
    //   <option value="${item.sku}">
    //     ${item.name} - ¥${item.price.toFixed(2)} (x${item.qty})
    //   </option>
    // `).join("")
    // 添加一个默认的 "Total" 选项
    orderSelect.innerHTML = `
      <option value="total" selected>Total (All Items)</option>
      ${cart.map(item => `
        <option value="${item.sku}">
          ${item.name} - ¥${item.price.toFixed(2)} (x${item.qty})
        </option>
      `).join("")}
    `
  }
  // // 默认数量填充为购物车第一个商品的数量
  // const qtyInput = formBox.querySelector("input[name='oqty']")
  // if (qtyInput && cart.length > 0) {
  //   qtyInput.value = cart[0].qty
  // }

  // Quantity 输入框逻辑
  const qtyInput = formBox.querySelector("input[name='oqty']")
  if (qtyInput) {
    // 默认显示总数量（所有商品数量之和）
    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0)
    qtyInput.value = totalQty

    // 当下拉框切换时，更新数量
    orderSelect.addEventListener("change", e => {
      const selectedValue = e.target.value
      if (selectedValue === "total") {
        qtyInput.value = cart.reduce((sum, item) => sum + item.qty, 0)
      } else {
        const selectedItem = cart.find(item => item.sku === selectedValue)
        qtyInput.value = selectedItem ? selectedItem.qty : 1
      }
    })
  }
})
