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

  if (typeof DB_PRODUCTS !== "undefined" && DB_PRODUCTS.length > 0) {
    renderProducts(DB_PRODUCTS)
    // order.php 的下拉由 PHP 直接渲染，此处无需调用 renderOrderOptions
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


