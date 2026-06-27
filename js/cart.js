// 全局购物车数组
let cart = []



// 封装加购物车函数
function addToCart (product) {
  // 检查购物车里是否已有该商品
  const existing = cart.find(item => item.sku === product.sku)
  if (existing) {
    existing.qty += 1 // 已有则数量+1
  } else {
    cart.push({ ...product, qty: 1 })
  }

  // console.log("Cart:", cart)
  renderCart() // 更新购物车UI

  // 保存购物车数据到 localStorage
  localStorage.setItem("cart", JSON.stringify(cart))
}


// 渲染购物车
function renderCart () {
  const cartGrid = document.querySelector(".cart-grid")
  cartGrid.innerHTML = "" // 清空购物车区

  if (cart.length === 0) {
    // 保留原有默认展示样式
    cartGrid.innerHTML = `
      <div class="card">
        <div class="card-body">
          <h3 class="card-title">Your Cart</h3>
          <p class="card-desc">View your selected furniture and proceed to order.</p>
          <div class="card-price">Ready to checkout</div>
          <a href="index.php" class="card-btn">Select Item</a>
        </div>
      </div>
    `
    return
  }

  cart.forEach(item => {
    const card = document.createElement("div")
    card.className = "card"

    card.innerHTML = `
      <div class="card-body">
        <h3 class="card-title">${item.name}</h3>
        <p class="card-desc">Model: ${item.model}</p>
        <div class="card-price">¥${item.price.toFixed(2)} × ${item.qty}</div>
        <button class="card-btn remove-btn">Remove</button>
      </div>
    `

    // 删除按钮逻辑
    card.querySelector(".remove-btn").addEventListener("click", () => {
      removeFromCart(item.sku)
    })

    cartGrid.appendChild(card)
  })
  // 总价显示
  const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0)
  const totalCard = document.createElement("div")
  totalCard.className = "card"
  totalCard.innerHTML = `
    <div class="card-body">
      <h3 class="card-title">Total</h3>
      <p class="card-desc">Summary of your cart</p>
      <div class="card-price">¥${total.toFixed(2)}</div>
      <a href="order.php" class="card-btn">Go to 🛒</a>
    </div>
  `
  cartGrid.appendChild(totalCard)
}

// 删购物车item
function removeFromCart (sku) {
  cart = cart.filter(item => item.sku !== sku)
  renderCart()

  // 保存购物车数据到 localStorage
  localStorage.setItem("cart", JSON.stringify(cart))
}



