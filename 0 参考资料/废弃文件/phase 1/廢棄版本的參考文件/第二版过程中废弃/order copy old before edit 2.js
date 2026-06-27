/* ============================================
   Order Page Logic - js/order.js
   ============================================ */

document.addEventListener("DOMContentLoaded", () => {

  refreshOrderSelect()

  // 从 localStorage 读取购物车数据
  const cartData = localStorage.getItem("cart")
  let cart = cartData ? JSON.parse(cartData) : []

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
        <div class="card-price">$${item.price.toFixed(2)} × ${item.qty}</div>
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

    orderSelect.innerHTML = `
      <option value="total" selected>Total (All Items)</option>
      ${cart.map(item => `
        <option value="${item.sku}">
          ${item.name} - ¥${item.price.toFixed(2)} (x${item.qty})
        </option>
      `).join("")}
    `
  }


  // Quantity 输入框逻辑
  const qtyInput = formBox.querySelector("input[name='oqty']")

  // 默认数量显示所有商品总数
  qtyInput.value = cart.reduce((sum, item) => sum + item.qty, 0)

  // 渲染选中商品或总价
  function renderSelectedItem (selectedValue) {
    // 清理之前插入的卡片
    const oldSummary = formBox.querySelector(".cart-summary")
    if (oldSummary) oldSummary.remove()

    const cartList = document.createElement("div")
    cartList.className = "cart-summary"

    if (selectedValue === "total") {
      // 渲染所有商品
      cart.forEach(item => {
        const card = createItemCard(item)
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
          <div class="card-price">
            $${total.toFixed(2)}
            <button class="card-btn edit-btn">Edit Cart</button>
          </div>
        </div>
      `

      // 编辑按钮逻辑：允许删除某个品种
      totalCard.querySelector(".edit-btn").addEventListener("click", () => {
        // 简单实现：清空购物车
        cart = []
        localStorage.setItem("cart", JSON.stringify(cart))
        renderSelectedItem("total")
        refreshOrderSelect()
      })

      cartList.appendChild(totalCard)

    } else {
      // 渲染单个商品
      const selectedItem = cart.find(item => item.sku === selectedValue)
      if (selectedItem) {
        const card = createItemCard(selectedItem)
        cartList.appendChild(card)
      }
    }

    formBox.insertBefore(cartList, formBox.firstChild)
  }

  // 创建商品卡片，带数量编辑按钮
  function createItemCard (item) {
    const card = document.createElement("div")
    card.className = "card"
    card.innerHTML = `
      <div class="card-body">
        <h3 class="card-title">${item.name}</h3>
        <p class="card-desc">Model: ${item.model}</p>
        <div class="card-price">
          $${item.price.toFixed(2)} × <span class="qty">${item.qty}</span>
          <button class="card-btn minus-btn">-</button>
          <button class="card-btn plus-btn">+</button>
        </div>
      </div>
    `

    // 数量编辑逻辑
    const qtySpan = card.querySelector(".qty")
    card.querySelector(".minus-btn").addEventListener("click", () => {
      if (item.qty > 1) {
        item.qty -= 1
        qtySpan.textContent = item.qty
        qtyInput.value = item.qty
        localStorage.setItem("cart", JSON.stringify(cart))
        refreshOrderSelect()
      }
    })
    card.querySelector(".plus-btn").addEventListener("click", () => {
      item.qty += 1
      qtySpan.textContent = item.qty
      qtyInput.value = item.qty
      localStorage.setItem("cart", JSON.stringify(cart))
      refreshOrderSelect()
    })

    return card
  }

  // 下拉框切换时渲染对应商品
  orderSelect.addEventListener("change", e => {
    const selectedValue = e.target.value
    if (selectedValue === "total") {
      qtyInput.value = cart.reduce((sum, item) => sum + item.qty, 0)
    } else {
      const selectedItem = cart.find(item => item.sku === selectedValue)
      qtyInput.value = selectedItem ? selectedItem.qty : 1
    }
    renderSelectedItem(selectedValue)
  })




  // 初始渲染默认选项（Total）
  renderSelectedItem("total")
})
