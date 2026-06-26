/* ============================================
   Main Page Logic - js/main.js
   ============================================ */


// 渲染订单选择下拉框
function renderOrderOptions (data) {
  const orderSelect = document.querySelector("select[name='fid']")
  if (!orderSelect) return

  orderSelect.innerHTML = data.map((product, index) => `
    <option value="${index + 1}">
      ${product.name} - ¥${product.price.toFixed(2)}
    </option>
  `).join("")
}

// 页面初始化
document.addEventListener("DOMContentLoaded", () => {
  // 渲染商品
  if (typeof MOCK_DATA !== "undefined" && MOCK_DATA.products) {
    renderProducts(MOCK_DATA.products)   // 来自 product.js
    renderOrderOptions(MOCK_DATA.products) // 仍在 main.js 或单独 order.js
  }

  // 状态检查：用户角色
  const role = localStorage.getItem("user_role") || "guest"
  // 如果你有 auth.js，可以在这里调用权限逻辑
  if (typeof handleAuthNav === "function") {
    handleAuthNav(role)
  }

  // 20260401 渲染购物车
  renderCart()


})


