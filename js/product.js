
// 渲染商品函数
function renderProducts (data) {
  const grid = document.getElementById("product-grid")
  grid.innerHTML = "" // 清空容器

  data.forEach((product, index) => {
    const card = document.createElement("div")
    card.className = "card"

    // 图片路径按索引匹配 assets/Sample_furntiure_images/1.png ~ 6.png
    const imagePath = `assets/Sample_furntiure_images/${index + 1}.png`

    // card.innerHTML = `
    //   <div class="card-img">
    //     <img src="${imagePath}" alt="${product.name}">
    //   </div>
    //   <div class="card-body">
    //     <h3 class="card-title">${product.name}</h3>
    //     <p class="card-desc">Model: ${product.model}</p>
    //     <div class="card-price">¥${product.price.toFixed(2)}</div>
    //     <label for="option-${product.sku}">Options:</label>
    //     <select id="option-${product.sku}" class="form-control">
    //       ${product.options.map(opt => `
    //         <option value="${opt.color}-${opt.material}">
    //           ${opt.color} / ${opt.material}
    //         </option>`).join("")}
    //     </select>

    //     <button class="card-btn">Add to Cart</button>
    //   </div>
    // `

    // 20260401 DOM学习
    card.innerHTML = `
      <div class="card-img zoom-container">
        <img src="${imagePath}" alt="${product.name}" class="zoom-img">
        <button class="zoom-btn" onclick="enableZoom(this)">Zoom 🔍</button>
      </div>
      <div class="card-body">
        <h3 class="card-title">${product.name}</h3>
        <p class="card-desc">Model: ${product.model}</p>
        <div class="card-price">¥${product.price.toFixed(2)}</div>
        <label for="option-${product.sku}">Options:</label>
        <select id="option-${product.sku}" class="form-control">
          ${product.options.map(opt => `
            <option value="${opt.color}-${opt.material}">
              ${opt.color} / ${opt.material}
            </option>`).join("")}
        </select>
        <button class="card-btn">Add to Cart</button>
      </div>
    `

    // 交互：监听选项变化
    const selectBox = card.querySelector(`#option-${product.sku}`)
    selectBox.addEventListener("change", e => {
      console.log(`SKU: ${product.sku}, Selected: ${e.target.value}`)
    })


    // 加购物车
    const addBtn = card.querySelector(".card-btn")
    addBtn.addEventListener("click", () => {
      addToCart(product) // 调用购物车逻辑
    })

    grid.appendChild(card)
  })

}

// item图片缩放
function enableZoom (btn) {
  const container = btn.closest('.zoom-container')
  const img = container.querySelector('.zoom-img')
  container.classList.add('active')

  container.addEventListener('mousemove', function (e) {
    const rect = container.getBoundingClientRect()
    const x = ((e.clientX - rect.left) / rect.width) * 100
    const y = ((e.clientY - rect.top) / rect.height) * 100
    img.style.transformOrigin = `${x}% ${y}%`
  })

  container.addEventListener('mouseleave', function () {
    container.classList.remove('active')
    img.style.transformOrigin = "center center"
  })
}
