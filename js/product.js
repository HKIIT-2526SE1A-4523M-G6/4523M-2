

function renderProducts (data) {
    const grid = document.getElementById("product-grid")
    if (!grid) return
    grid.innerHTML = ""
    data.forEach(product => {
        const card = document.createElement("div")
        card.className = "card"
        const inStock = product.availableStock > 0
        // options 下拉：DB暂无时显示占位
        const optionsHtml = product.options && product.options.length > 0
            ? `<label for="option-${product.sku}">Options:</label>
       <select id="option-${product.sku}" class="form-control">
         ${product.options.map(opt => `
           <option value="${opt.color}-${opt.material}">
             ${opt.color} / ${opt.material}
           </option>`).join("")}
       </select>`
            : "" // DB无选项时不渲染下拉框
        card.innerHTML = `
    <div class="card-img zoom-container">
      <img src="${product.image}" alt="${product.name}" class="zoom-img">
      <button class="zoom-btn" onclick="enableZoom(this)">Zoom 🔍</button>
    </div>
    <div class="card-body">
      <h3 class="card-title">${product.name}</h3>
      <p class="card-desc">Model: ${product.model}</p>
      <div class="card-price">$${product.price.toFixed(2)}</div>
      ${optionsHtml}
      ${inStock
                ? `<p style="color:green;font-weight:bold;margin:5px 0;">Available Stock: ${product.availableStock}</p>
           <button class="card-btn add-btn">Add to Cart</button>`
                : `<p style="color:red;font-weight:bold;margin:5px 0;">Sold Out</p>
           <button class="card-btn" style="background:#ccc;cursor:not-allowed;" disabled>Out of Stock</button>`
            }
    </div>
  `
        // options 事件（仅当有下拉时）
        const selectBox = card.querySelector(`#option-${product.sku}`)
        if (selectBox) {
            selectBox.addEventListener("change", e => {
                console.log(`SKU: ${product.sku}, Selected: ${e.target.value}`)
            })
        }
        // 加购物车（仅有库存时）
        const addBtn = card.querySelector(".add-btn")
        if (addBtn) {
            addBtn.addEventListener("click", () => {
                addToCart(product)
            })
        }
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
