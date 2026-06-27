// /* ============================================
//    Auth Logic Demo - js/auth.js
//    ============================================ */


/* js/auth.js
   仅保留 login.php 的 tab 切换逻辑。
   导航栏已由 PHP $_SESSION 服务端渲染，不再由 JS 接管。
*/
document.addEventListener("DOMContentLoaded", () => {
  const customerTab = document.getElementById("customer-tab")
  const adminTab = document.getElementById("admin-tab")
  const customerContent = document.getElementById("customer-content")
  const adminContent = document.getElementById("admin-content")

  if (!customerTab || !adminTab) return   // 非 login 页面直接退出

  customerTab.addEventListener("click", () => {
    customerTab.classList.add("active")
    adminTab.classList.remove("active")
    customerContent.classList.add("active")
    adminContent.classList.remove("active")
  })

  adminTab.addEventListener("click", () => {
    adminTab.classList.add("active")
    customerTab.classList.remove("active")
    adminContent.classList.add("active")
    customerContent.classList.remove("active")
  })

  // 渲染商品列表（index.php 注入 DB_PRODUCTS）
  if (typeof DB_PRODUCTS !== "undefined" && DB_PRODUCTS.length > 0) {
    renderProducts(DB_PRODUCTS)
  }

  // 渲染购物车（index.php cart 区域）
  if (typeof renderCart === "function") {
    renderCart()
  }
})