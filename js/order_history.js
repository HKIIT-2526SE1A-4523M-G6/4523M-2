/* js/order_history.js — 订单历史 AJAX 删单 */
(function () {
    function showMsg(text, ok) {
        const el = document.getElementById('history-msg')
        if (!el) return
        el.innerHTML = `<div class="alert ${ok?'alert-success':'alert-error'}">${text}</div>`
        if (ok) setTimeout(() => { el.innerHTML = '' }, 4000)
    }

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const oid = btn.dataset.orderId
            const ok  = await showConfirm(
                'Delete Order #' + oid,
                'Are you sure? Stock will be restored. This cannot be undone.'
            )
            if (!ok) return

            btn.disabled = true; btn.textContent = 'Deleting…'

            try {
                const res  = await fetch('order_history.php', {
                    method:  'POST',
                    headers: {'Content-Type':'application/json'},
                    body:    JSON.stringify({orderID: parseInt(oid)})
                })
                const data = await res.json()
                if (data.ok) {
                    const row = document.querySelector(`tr[data-order-id="${oid}"]`)
                    if (row) row.remove()
                    showMsg(data.msg, true)
                    if (document.querySelectorAll('#history-table tbody tr').length === 0) {
                        document.querySelector('#history-table tbody').innerHTML =
                            '<tr id="empty-row"><td colspan="9">No orders found.</td></tr>'
                    }
                } else {
                    showMsg(data.msg, false)
                    btn.disabled = false; btn.textContent = 'Delete'
                }
            } catch (e) {
                showMsg('Network error. Please try again.', false)
                btn.disabled = false; btn.textContent = 'Delete'
            }
        })
    })
})()