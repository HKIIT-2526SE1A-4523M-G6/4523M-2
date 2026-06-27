// /* ============================================
//    Auth Logic Demo - js/auth.js
//    ============================================ */

// // 简单的登录验证函数
// function login (username, password) {
//   if (!MOCK_DATA || !MOCK_DATA.users) {
//     console.error("MOCK_DATA.users 未定义")
//     return null
//   }

//   const user = MOCK_DATA.users.find(
//     u => u.username === username && u.password === password
//   )

//   if (user) {
//     // 登录成功，保存角色到 localStorage
//     localStorage.setItem("user_role", user.role)
//     console.log(`Login success: ${user.username} (${user.role})`)
//     return user
//   } else {
//     console.warn("Login failed: 用户名或密码错误")
//     return null
//   }
// }

// // 导航栏权限切换逻辑
// function handleAuthNav (role) {
//   const navLinks = document.querySelector(".nav-links")
//   if (!navLinks) return

//   // 清空导航栏
//   navLinks.innerHTML = ""

//   // 公共导航
//   navLinks.innerHTML += `<li><a href="index.php">Home</a></li>`

//   if (role === "customer") {
//     navLinks.innerHTML += `<li><a href="order.php">Cart</a></li>`
//     navLinks.innerHTML += `<li><a href="login.php">Logout</a></li>`
//   } else if (role === "staff") {
//     navLinks.innerHTML += `<li><a href="admin.php">Admin Panel</a></li>`
//     navLinks.innerHTML += `<li><a href="login.php">Logout</a></li>`
//   } else {
//     // 游客
//     navLinks.innerHTML += `<li><a href="login.php">Login</a></li>`
//     navLinks.innerHTML += `<li><a href="register.php">Register</a></li>`
//     navLinks.innerHTML += `<li><a href="order.php">Cart</a></li>`
//   }
// }

// // 页面初始化时检查登录状态
// document.addEventListener("DOMContentLoaded", () => {
//   const role = localStorage.getItem("user_role") || "guest"
//   handleAuthNav(role)
// })


// // 标签切换逻辑
// document.addEventListener("DOMContentLoaded", () => {
//   const customerTab = document.getElementById("customer-tab")
//   const adminTab = document.getElementById("admin-tab")
//   const customerContent = document.getElementById("customer-content")
//   const adminContent = document.getElementById("admin-content")

//   customerTab.addEventListener("click", () => {
//     customerTab.classList.add("active")
//     adminTab.classList.remove("active")
//     customerContent.classList.add("active")
//     adminContent.classList.remove("active")
//   })

//   adminTab.addEventListener("click", () => {
//     adminTab.classList.add("active")
//     customerTab.classList.remove("active")
//     adminContent.classList.add("active")
//     customerContent.classList.remove("active")
//   })
// });

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