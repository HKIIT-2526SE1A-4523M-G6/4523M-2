/* ============================================
   Auth Logic Demo - js/auth.js
   ============================================ */

// 简单的登录验证函数
function login (username, password) {
  if (!MOCK_DATA || !MOCK_DATA.users) {
    console.error("MOCK_DATA.users 未定义")
    return null
  }

  const user = MOCK_DATA.users.find(
    u => u.username === username && u.password === password
  )

  if (user) {
    // 登录成功，保存角色到 localStorage
    localStorage.setItem("user_role", user.role)
    console.log(`Login success: ${user.username} (${user.role})`)
    return user
  } else {
    console.warn("Login failed: 用户名或密码错误")
    return null
  }
}

// 导航栏权限切换逻辑
function handleAuthNav (role) {
  const navLinks = document.querySelector(".nav-links")
  if (!navLinks) return

  // 清空导航栏
  navLinks.innerHTML = ""

  // 公共导航
  navLinks.innerHTML += `<li><a href="index.html">Home</a></li>`

  if (role === "customer") {
    navLinks.innerHTML += `<li><a href="order.html">Cart</a></li>`
    navLinks.innerHTML += `<li><a href="login.html">Logout</a></li>`
  } else if (role === "staff") {
    navLinks.innerHTML += `<li><a href="admin.html">Admin Panel</a></li>`
    navLinks.innerHTML += `<li><a href="login.html">Logout</a></li>`
  } else {
    // 游客
    navLinks.innerHTML += `<li><a href="login.html">Login</a></li>`
    navLinks.innerHTML += `<li><a href="register.html">Register</a></li>`
    navLinks.innerHTML += `<li><a href="order.html">Cart</a></li>`
  }
}

// 页面初始化时检查登录状态
document.addEventListener("DOMContentLoaded", () => {
  const role = localStorage.getItem("user_role") || "guest"
  handleAuthNav(role)
})


// 标签切换逻辑
document.addEventListener("DOMContentLoaded", () => {
  const customerTab = document.getElementById("customer-tab")
  const adminTab = document.getElementById("admin-tab")
  const customerContent = document.getElementById("customer-content")
  const adminContent = document.getElementById("admin-content")

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
});

