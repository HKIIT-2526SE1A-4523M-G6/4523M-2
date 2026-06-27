/* js/admin_orders.js — 订单管理 AJAX 更新 */
(function () {
    const statusLabels = {1:'Pending',2:'Processing',3:'Delivering',4:'Completed',5:'Cancelled'}

    function showMsg(text, ok) {
        const el = document.getElementById('admin-msg')
        if (!el) return
        el.innerHTML = `<div class="alert ${ok?'alert-success':'alert-error'}">${text}</div>`
        setTimeout(() => { el.innerHTML = '' }, 4000)
    }

    document.querySelectorAll('.update-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const oid = parseInt(btn.dataset.orderId)
            const fid = parseInt(btn.dataset.furnitureId)
            const row = btn.closest('tr')

            const qtyInput    = row.querySelector('.input-qty')
            const statusSel   = row.querySelector('.select-status')
            const newQty      = parseInt(qtyInput.value)
            const newStatus   = parseInt(statusSel.value)

            if (isNaN(newQty) || newQty < 1) {
                showMsg('Quantity must be at least 1.', false); return
            }

            btn.disabled    = true
            btn.textContent = 'Saving…'

            try {
                const res  = await fetch('admin.php', {
                    method:  'POST',
                    headers: {'Content-Type':'application/json'},
                    body:    JSON.stringify({orderID:oid, furnitureID:fid, new_qty:newQty, new_status:newStatus})
                })
                const data = await res.json()

                if (data.ok) {
                    // 内联更新对应单元格
                    row.querySelector('.cell-qty').textContent    = data.newQty
                    row.querySelector('.cell-total').textContent  = '$' + data.newTotal
                    row.querySelector('.cell-status').innerHTML   = '<strong>' + (statusLabels[data.newStatus]||'Unknown') + '</strong>'
                    showMsg(data.msg, true)
                } else {
                    showMsg(data.msg, false)
                }
            } catch (e) {
                showMsg('Network error. Please try again.', false)
            } finally {
                btn.disabled    = false
                btn.textContent = 'Update'
            }
        })
    })
})()