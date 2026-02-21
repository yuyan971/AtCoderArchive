(function() {
    'use strict';

    function initPasswordToggle() {
        var section = document.getElementById('password-section');
        var toggle = document.getElementById('password-toggle');
        if (!section || !toggle) return;
        toggle.addEventListener('click', function() {
            var isOpen = section.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.textContent = isOpen ? 'Close' : 'Change Password';
        });
    }

    function initLeaveConfirm() {
        var btn = document.getElementById('btn-leave');
        var form = document.getElementById('leave-form');
        if (!btn || !form) return;
        btn.addEventListener('click', function() {
            if (window.confirm('本当に退会しますか？この操作は取り消せません。')) {
                form.submit();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initPasswordToggle();
            initLeaveConfirm();
        });
    } else {
        initPasswordToggle();
        initLeaveConfirm();
    }
})();
