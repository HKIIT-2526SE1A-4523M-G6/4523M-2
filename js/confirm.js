/* ============================================
   Reusable Confirm Modal - js/confirm.js
   Depends on: #confirm-overlay markup in page
   (injected automatically if not present)
   ============================================ */

(function () {
    // Inject modal HTML if not already in DOM
    if (!document.getElementById('confirm-overlay')) {
        var html = '<div id="confirm-overlay">' +
            '<div id="confirm-box">' +
            '<h3 id="confirm-title">Are you sure?</h3>' +
            '<p id="confirm-message"></p>' +
            '<div class="confirm-actions">' +
            '<button id="confirm-ok">Confirm</button>' +
            '<button id="confirm-cancel">Cancel</button>' +
            '</div></div></div>';
        document.body.insertAdjacentHTML('beforeend', html);
    }

    var overlay  = document.getElementById('confirm-overlay');
    var titleEl  = document.getElementById('confirm-title');
    var msgEl    = document.getElementById('confirm-message');
    var okBtn    = document.getElementById('confirm-ok');
    var cancelBtn = document.getElementById('confirm-cancel');
    var _resolve = null;

    function closeModal() {
        overlay.classList.remove('show');
        _resolve = null;
    }

    okBtn.addEventListener('click', function () {
        if (_resolve) _resolve(true);
        closeModal();
    });

    cancelBtn.addEventListener('click', function () {
        if (_resolve) _resolve(false);
        closeModal();
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            if (_resolve) _resolve(false);
            closeModal();
        }
    });

    /**
     * showConfirm(title, message) → Promise<boolean>
     * Usage:
     *   showConfirm('Delete Order', 'This cannot be undone.').then(function(ok) {
     *     if (ok) { ... }
     *   });
     */
    window.showConfirm = function (title, message) {
        titleEl.textContent  = title   || 'Are you sure?';
        msgEl.textContent    = message || '';
        overlay.classList.add('show');
        return new Promise(function (resolve) {
            _resolve = resolve;
        });
    };
})();
