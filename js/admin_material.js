/* js/admin_material.js — 物料管理 AJAX 新增 */
(function () {
    function showMsg(text, ok) {
        const el = document.getElementById('material-msg')
        if (!el) return
        el.innerHTML = `<div class="alert ${ok?'alert-success':'alert-error'}">${text}</div>`
        if (ok) setTimeout(() => { el.innerHTML = '' }, 4000)
    }

    document.getElementById('add-material-btn').addEventListener('click', async () => {
        const name = document.getElementById('m-name').value.trim()
        const qty  = parseInt(document.getElementById('m-qty').value)
        const unit = document.getElementById('m-unit').value.trim()

        if (!name || !unit) { showMsg('Name and Unit are required.', false); return }
        if (isNaN(qty) || qty < 0) { showMsg('Quantity cannot be negative.', false); return }

        const btn = document.getElementById('add-material-btn')
        btn.disabled = true; btn.textContent = 'Adding…'

        try {
            const res  = await fetch('admin_material.php', {
                method:  'POST',
                headers: {'Content-Type':'application/json'},
                body:    JSON.stringify({materialName:name, materialPhysicalQty:qty, materialUnit:unit})
            })
            const data = await res.json()
            if (data.ok) {
                showMsg(data.msg, true)
                prependMaterialRow(data.row)
                document.getElementById('m-name').value = ''
                document.getElementById('m-qty').value  = '0'
                document.getElementById('m-unit').value = ''
            } else {
                showMsg(data.msg, false)
            }
        } catch (e) {
            showMsg('Network error. Please try again.', false)
        } finally {
            btn.disabled = false; btn.textContent = 'Add Material'
        }
    })

    function prependMaterialRow(m) {
        const tbody    = document.querySelector('#material-table tbody')
        const emptyRow = document.getElementById('empty-row')
        if (emptyRow) emptyRow.remove()
        const tr = document.createElement('tr')
        tr.dataset.mid = m.materialID
        tr.innerHTML = `
      <td>${m.materialID}</td>
      <td>${m.materialName}</td>
      <td class="cell-qty">${m.materialPhysicalQty}</td>
      <td>${m.materialUnit}</td>`
        tbody.prepend(tr)
    }
})()