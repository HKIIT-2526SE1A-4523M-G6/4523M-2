/* js/admin_furniture.js — 家具管理 AJAX 增删 */
(function () {

    function showMsg(text, ok) {
        const el = document.getElementById('furniture-msg')
        if (!el) return
        el.innerHTML = `<div class="alert ${ok?'alert-success':'alert-error'}">${text}</div>`
        if (ok) setTimeout(() => { el.innerHTML = '' }, 4000)
    }

    /* ── BOM 动态行 ── */
    document.getElementById('add-bom-row').addEventListener('click', () => {
        const row = document.createElement('div')
        row.className = 'bom-row'
        row.innerHTML = `<select name="mat_id[]" class="form-control">${MAT_OPTIONS_HTML}</select>
      <input type="number" name="mat_qty[]" class="form-control" min="1" value="1">
      <button type="button" class="btn-remove-bom" onclick="removeBomRow(this)">✕</button>`
        document.getElementById('bom-container').appendChild(row)
    })

    window.removeBomRow = function(btn) {
        const rows = document.querySelectorAll('#bom-container .bom-row')
        if (rows.length <= 1) { alert('At least one material row is required.'); return }
        btn.closest('.bom-row').remove()
    }

    /* ── 新增家具 ── */
    document.getElementById('add-furniture-btn').addEventListener('click', async () => {
        const fname  = document.getElementById('f-name').value.trim()
        const fprice = parseFloat(document.getElementById('f-price').value)

        if (!fname || isNaN(fprice) || fprice <= 0) {
            showMsg('Name and a valid Price are required.', false); return
        }

        // 收集 BOM
        const bom = []
        document.querySelectorAll('#bom-container .bom-row').forEach(row => {
            const mid = parseInt(row.querySelector('select').value)
            const qty = parseInt(row.querySelector('input').value)
            if (mid > 0 && qty > 0) bom.push({ materialID: mid, qty })
        })
        if (bom.length === 0) {
            showMsg('At least one valid BOM material is required.', false); return
        }

        const btn = document.getElementById('add-furniture-btn')
        btn.disabled = true; btn.textContent = 'Adding…'

        try {
            const res  = await fetch('admin_furniture.php', {
                method:  'POST',
                headers: {'Content-Type':'application/json'},
                body:    JSON.stringify({
                    action: 'add',
                    furnitureName:        fname,
                    furnitureModel:       document.getElementById('f-model').value.trim(),
                    furnitureDescription: document.getElementById('f-desc').value.trim(),
                    furniturePrice:       fprice,
                    furnitureImage:       document.getElementById('f-image').value.trim(),
                    furnitureCategory:    document.getElementById('f-category').value.trim(),
                    bom
                })
            })
            const data = await res.json()

            if (data.ok) {
                showMsg(data.msg, true)
                prependFurnitureRow(data.row)
                // 清空表单
                ;['f-name','f-model','f-desc','f-price','f-image','f-category'].forEach(id => {
                    document.getElementById(id).value = ''
                })
                // 重置 BOM 到一行
                document.getElementById('bom-container').innerHTML = `
          <div class="bom-row">
            <select name="mat_id[]" class="form-control">${MAT_OPTIONS_HTML}</select>
            <input type="number" name="mat_qty[]" class="form-control" min="1" value="1">
            <button type="button" class="btn-remove-bom" onclick="removeBomRow(this)">✕</button>
          </div>`
            } else {
                showMsg(data.msg, false)
            }
        } catch (e) {
            showMsg('Network error. Please try again.', false)
        } finally {
            btn.disabled = false; btn.textContent = 'Add Furniture'
        }
    })

    /* ── 向表格顶部插入新行 ── */
    function prependFurnitureRow(f) {
        const tbody   = document.querySelector('#furniture-table tbody')
        const emptyRow = document.getElementById('empty-row')
        if (emptyRow) emptyRow.remove()

        const imgSrc = '1_Resources/furntiure_images/' + (f.furnitureImage || 'default.png')
        const tr = document.createElement('tr')
        tr.dataset.fid = f.furnitureID
        tr.innerHTML = `
      <td>${f.furnitureID}</td>
      <td>${f.furnitureSKU}</td>
      <td><img src="${imgSrc}" class="table-img" onerror="this.style.display='none'" alt=""></td>
      <td>${f.furnitureName}</td>
      <td>$${parseFloat(f.furniturePrice).toFixed(2)}</td>
      <td style="font-size:12px;text-align:left;">${f.bom || '—'}</td>
      <td><button class="btn-delete del-f-btn"
                  data-fid="${f.furnitureID}"
                  data-fname="${f.furnitureName}">Delete</button></td>`
        tbody.prepend(tr)
        bindDeleteBtn(tr.querySelector('.del-f-btn'))
    }

    /* ── 删除家具 ── */
    function bindDeleteBtn(btn) {
        btn.addEventListener('click', async () => {
            const fid   = btn.dataset.fid
            const fname = btn.dataset.fname
            const ok = await showConfirm(
                'Delete Furniture #' + fid,
                `Delete "${fname}"? Only allowed if no existing orders.`
            )
            if (!ok) return

            try {
                const res  = await fetch('admin_furniture.php', {
                    method:  'POST',
                    headers: {'Content-Type':'application/json'},
                    body:    JSON.stringify({action:'delete', furnitureID: parseInt(fid)})
                })
                const data = await res.json()
                if (data.ok) {
                    const row = document.querySelector(`tr[data-fid="${fid}"]`)
                    if (row) row.remove()
                    showMsg(data.msg, true)
                    // 如果表格空了，显示空提示
                    if (document.querySelectorAll('#furniture-table tbody tr').length === 0) {
                        document.querySelector('#furniture-table tbody').innerHTML =
                            '<tr id="empty-row"><td colspan="7">No furniture found.</td></tr>'
                    }
                } else {
                    showMsg(data.msg, false)
                }
            } catch (e) {
                showMsg('Network error. Please try again.', false)
            }
        })
    }

    // 绑定页面初始已有的删除按钮
    document.querySelectorAll('.del-f-btn').forEach(bindDeleteBtn)
})()