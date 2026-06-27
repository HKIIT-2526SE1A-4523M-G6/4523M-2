/* js/profile.js — 个人资料 AJAX 更新 */
(function () {
    function showMsg(text, ok) {
        const el = document.getElementById('profile-msg')
        if (!el) return
        el.innerHTML = `<div class="alert ${ok?'alert-success':'alert-error'}">${text}</div>`
        if (ok) setTimeout(() => { el.innerHTML = '' }, 4000)
    }

    document.getElementById('save-profile-btn').addEventListener('click', async () => {
        const pwd     = document.getElementById('p-password').value.trim()
        const contact = document.getElementById('p-contact').value.trim()
        const address = document.getElementById('p-address').value.trim()

        if (!pwd && !contact && !address) {
            showMsg('Please fill in at least one field.', false); return
        }

        const btn = document.getElementById('save-profile-btn')
        btn.disabled = true; btn.textContent = 'Saving…'

        try {
            const res  = await fetch('profile.php', {
                method:  'POST',
                headers: {'Content-Type':'application/json'},
                body:    JSON.stringify({new_password:pwd, new_contact:contact, new_address:address})
            })
            const data = await res.json()

            if (data.ok) {
                showMsg(data.msg, true)
                // 内联更新只读区
                const p = data.profile
                if (p) {
                    document.getElementById('display-contact').textContent = p.customerNumber  || ''
                    document.getElementById('display-address').textContent = p.customerAddress || ''
                    document.getElementById('display-email').textContent   = p.customerEmail   || '—'
                }
                // 清空输入框
                document.getElementById('p-password').value = ''
                document.getElementById('p-contact').value  = ''
                document.getElementById('p-address').value  = ''
            } else {
                showMsg(data.msg, false)
            }
        } catch (e) {
            showMsg('Network error. Please try again.', false)
        } finally {
            btn.disabled = false; btn.textContent = 'Save Changes'
        }
    })
})()