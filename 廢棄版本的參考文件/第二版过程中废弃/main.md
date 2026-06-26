/* ============================================
   Main Page Logic - js/main.js
   ============================================ */

// 渲染商品函数
function renderProducts (data) {
  const grid = document.getElementById("product-grid")
  grid.innerHTML = "" // 清空容器

  data.forEach((product, index) => {
    const card = document.createElement("div")
    card.className = "card"

    // 使用 index 对应图片文件名 (1.png ~ 6.png)
    const imagePath = `assets/Sample_furntiure_images/${index + 1}.png`

    // // <img src="assets/images/${product.sku}.jpg" alt="${product.name}">

    // 商品卡片 HTML
    card.innerHTML = `
      <img src="${imagePath}" alt="${product.name}">
      <h3>${product.name}</h3>
      <p>Model: ${product.model}</p>
      <p>Price: $${product.price.toFixed(2)}</p>
      <p>Status: ${product.stockStatus}</p>
      <label for="option-${product.sku}">Options:</label>
      <select id="option-${product.sku}">
        ${product.options.map(opt => `
          <option value="${opt.color}-${opt.material}">
            ${opt.color} / ${opt.material}
          </option>`).join("")}
      </select>
      <button>Add to Cart</button>
    `

    // 交互：监听选项变化
    const selectBox = card.querySelector(`#option-${product.sku}`)
    selectBox.addEventListener("change", e => {
      console.log(`SKU: ${product.sku}, Selected: ${e.target.value}`)
    })

    grid.appendChild(card)
  })
}

// 页面初始化
document.addEventListener("DOMContentLoaded", () => {
  // 渲染商品
  if (typeof MOCK_DATA !== "undefined" && MOCK_DATA.products) {
    renderProducts(MOCK_DATA.products)
  }

  // 状态检查：用户角色
  const role = localStorage.getItem("user_role") || "guest"
  if (typeof handleAuthNav === "function") {
    handleAuthNav(role) // 调用 auth.js 中的权限逻辑
  }
})
