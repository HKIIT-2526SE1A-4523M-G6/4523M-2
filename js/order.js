/* ============================================
   Order Page Logic - js/order.js
   ============================================ */


/* js/order.js
   整车购物车下单页逻辑。
   依赖：ORDER_SESSION（由 order.php 注入）
*/
document.addEventListener("DOMContentLoaded", () => {
  const app = document.getElementById("order-app")
  if (!app) return

  // ── 读购物车 ──
  let cart = []
  try {
    cart = JSON.parse(localStorage.getItem("cart") || "[]")
  } catch (e) {
    cart = []
  }

  // ── 未登录：提示 ──
  if (!ORDER_SESSION.isCustomer) {
    app.innerHTML = `
      <div class="form-box" style="text-align:center;padding:40px;">
        <p style="color:#666;margin-bottom:16px;">Please log in as a customer to place an order.</p>
        <a href="login.php" class="card-btn" style="display:inline-block;width:auto;padding:10px 28px;">Login</a>
      </div>`
    return
  }

  // ── 购物车为空：提示 ──
  if (cart.length === 0) {
    app.innerHTML = `
      <div class="form-box" style="text-align:center;padding:40px;">
        <p style="color:#666;margin-bottom:16px;">Your cart is empty.</p>
        <a href="index.php" class="card-btn" style="display:inline-block;width:auto;padding:10px 28px;">Browse Furniture</a>
      </div>`
    return
  }

  // ── 渲染主界面 ──
  render()

  function render () {
    const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0)

    app.innerHTML = `
      <div class="form-box">
        <h3 class="form-title">Order Details</h3>
        <div id="msg-area"></div>

        <!-- 购物车商品列表 -->
        <div id="cart-summary"></div>

        <!-- 总价 -->
        <div style="text-align:right;font-size:18px;font-weight:700;margin:12px 0 20px;">
          Order Total: <span id="total-display">$${total.toFixed(2)}</span>
        </div>

        <!-- 收货信息 -->
        <div class="form-group">
          <label>Delivery Address</label>
          <textarea class="form-control" id="input-address" rows="2" required></textarea>
        </div>
        <div class="form-group">
          <label>Delivery Date</label>
          <input type="datetime-local" class="form-control" id="input-date" required>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <button class="btn-submit" id="submit-btn">Submit Order</button>
          <button class="btn-submit" id="clear-btn"
            style="background:#e74c3c;">Clear Cart</button>
        </div>
      </div>`

    renderCartSummary()
    bindEvents()
  }

  function renderCartSummary () {
    const container = document.getElementById("cart-summary")
    if (!container) return
    container.innerHTML = ""

    cart.forEach(item => {
      const row = document.createElement("div")
      row.className = "card"
      row.style.marginBottom = "10px"
      row.innerHTML = `
        <div class="card-body" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
          <div style="flex:1;min-width:160px;">
            <div class="card-title" style="margin:0 0 4px;">${item.name}</div>
            <div style="color:#999;font-size:13px;">$${item.price.toFixed(2)} / unit</div>
          </div>
          <div style="display:flex;align-items:center;gap:8px;">
            <button class="card-btn minus-btn" data-sku="${item.sku}"
              style="width:32px;padding:4px 0;font-size:16px;">−</button>
            <span class="qty-display" data-sku="${item.sku}"
              style="min-width:24px;text-align:center;font-weight:700;">${item.qty}</span>
            <button class="card-btn plus-btn" data-sku="${item.sku}"
              style="width:32px;padding:4px 0;font-size:16px;">+</button>
          </div>
          <div style="font-weight:700;min-width:80px;text-align:right;">
            $${(item.price * item.qty).toFixed(2)}
          </div>
          <button class="card-btn remove-btn" data-sku="${item.sku}"
            style="background:#e74c3c;width:auto;padding:6px 14px;">Remove</button>
        </div>`
      container.appendChild(row)
    })
  }

  function updateTotalDisplay () {
    const el = document.getElementById("total-display")
    if (el) {
      const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0)
      el.textContent = "$" + total.toFixed(2)
    }
    // 同步写回 localStorage
    localStorage.setItem("cart", JSON.stringify(cart))
  }

  function bindEvents () {
    const summary = document.getElementById("cart-summary")

    // 数量 −
    summary.addEventListener("click", e => {
      const btn = e.target.closest(".minus-btn")
      if (!btn) return
      const sku = btn.dataset.sku
      const item = cart.find(i => i.sku === sku)
      if (item && item.qty > 1) {
        item.qty--
        summary.querySelector(`.qty-display[data-sku="${sku}"]`).textContent = item.qty
        updateTotalDisplay()
      }
    })

    // 数量 +
    summary.addEventListener("click", e => {
      const btn = e.target.closest(".plus-btn")
      if (!btn) return
      const sku = btn.dataset.sku
      const item = cart.find(i => i.sku === sku)
      if (item) {
        item.qty++
        summary.querySelector(`.qty-display[data-sku="${sku}"]`).textContent = item.qty
        updateTotalDisplay()
      }
    })

    // 单行移除
    summary.addEventListener("click", e => {
      const btn = e.target.closest(".remove-btn")
      if (!btn) return
      const sku = btn.dataset.sku
      cart = cart.filter(i => i.sku !== sku)
      localStorage.setItem("cart", JSON.stringify(cart))
      if (cart.length === 0) {
        render()   // 重新渲染，触发空购物车提示
      } else {
        renderCartSummary()
        updateTotalDisplay()
      }
    })

    // 清空购物车
    document.getElementById("clear-btn").addEventListener("click", () => {
      cart = []
      localStorage.setItem("cart", JSON.stringify(cart))
      render()
    })

    // 提交订单
    document.getElementById("submit-btn").addEventListener("click", submitOrder)
  }

  async function submitOrder () {
    const msgArea = document.getElementById("msg-area")
    const addressEl = document.getElementById("input-address")
    const dateEl = document.getElementById("input-date")
    const submitBtn = document.getElementById("submit-btn")

    const address = addressEl ? addressEl.value.trim() : ""
    const deliveryDate = dateEl ? dateEl.value.trim() : ""

    // 前端校验
    if (!address) {
      showMsg("Please enter a delivery address.", "error")
      return
    }
    if (!deliveryDate) {
      showMsg("Please select a delivery date.", "error")
      return
    }

    // 构建 payload：把 sku 转成 furnitureID
    const items = cart.map(item => ({
      furnitureID: item.furnitureID,
      qty: item.qty
    }))

    submitBtn.disabled = true
    submitBtn.textContent = "Submitting…"

    try {
      const res = await fetch("order.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ items, address, deliveryDate })
      })
      const data = await res.json()

      if (data.ok) {
        // 清空购物车
        cart = []
        localStorage.setItem("cart", JSON.stringify(cart))

        // 展示成功状态，不刷页
        app.innerHTML = `
          <div class="form-box" style="text-align:center;padding:48px;">
            <div style="font-size:48px;margin-bottom:16px;">✅</div>
            <h3 style="color:#27ae60;margin-bottom:8px;">Order Placed!</h3>
            <p style="color:#666;margin-bottom:4px;">${data.msg}</p>
            <p style="color:#999;font-size:13px;">Order ID: #${data.orderID}</p>
            <div style="margin-top:24px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
              <a href="order_history.php" class="card-btn" style="width:auto;padding:10px 24px;">My Orders</a>
              <a href="index.php"         class="card-btn" style="width:auto;padding:10px 24px;background:#888;">Keep Shopping</a>
            </div>
          </div>`
      } else {
        showMsg(data.msg, "error")
        submitBtn.disabled = false
        submitBtn.textContent = "Submit Order"
      }
    } catch (err) {
      showMsg("Network error. Please try again.", "error")
      submitBtn.disabled = false
      submitBtn.textContent = "Submit Order"
    }
  }

  function showMsg (text, type) {
    const area = document.getElementById("msg-area")
    if (!area) return
    const color = type === "error" ? "#e74c3c" : "#27ae60"
    area.innerHTML = `<div style="color:${color};font-weight:bold;margin-bottom:12px;padding:10px;
      background:${type === "error" ? "#fdf0f0" : "#f0fdf4"};border-radius:6px;">${text}</div>`
  }
})