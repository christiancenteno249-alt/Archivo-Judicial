(function () {
    if (window.appAlerts) return;

    var styles = `
    .app-alerts-overlay {
        position: fixed;
        inset: 0;
        background: rgba(3, 20, 43, 0.6);
        backdrop-filter: blur(2px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        padding: 20px;
    }
    .app-alerts-overlay.show {
        display: flex;
    }
    .app-alerts-modal {
        width: min(520px, 100%);
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #dbe7ff;
        box-shadow: 0 20px 50px rgba(0, 45, 98, 0.3);
        overflow: hidden;
        animation: appAlertsIn 0.18s ease-out;
    }
    @keyframes appAlertsIn {
        from { transform: translateY(8px) scale(0.98); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }
    .app-alerts-header {
        padding: 16px 20px 10px;
        border-bottom: 1px solid #eef3ff;
    }
    .app-alerts-title {
        margin: 0;
        color: #003a7c;
        font-size: 1.1rem;
        font-weight: 700;
    }
    .app-alerts-body {
        padding: 14px 20px 18px;
        color: #1f2d3d;
        font-size: 0.96rem;
        line-height: 1.5;
    }
    .app-alerts-footer {
        padding: 12px 20px 18px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .app-alerts-btn {
        border: 0;
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 600;
        cursor: pointer;
    }
    .app-alerts-btn-secondary {
        background: #eef2f8;
        color: #2f3d4f;
    }
    .app-alerts-btn-primary {
        background: #004085;
        color: #ffffff;
    }
    .app-alerts-type-info .app-alerts-title { color: #004085; }
    .app-alerts-type-success .app-alerts-title { color: #0f7a36; }
    .app-alerts-type-warning .app-alerts-title { color: #8a5a00; }
    .app-alerts-type-danger .app-alerts-title { color: #b3261e; }
    `;

    var styleTag = document.createElement("style");
    styleTag.textContent = styles;
    document.head.appendChild(styleTag);

    var overlay = document.createElement("div");
    overlay.className = "app-alerts-overlay";
    overlay.innerHTML = `
        <div class="app-alerts-modal app-alerts-type-info" role="dialog" aria-modal="true" aria-live="assertive">
            <div class="app-alerts-header">
                <h5 class="app-alerts-title">Notificacion</h5>
            </div>
            <div class="app-alerts-body"></div>
            <div class="app-alerts-footer">
                <button type="button" class="app-alerts-btn app-alerts-btn-secondary" data-role="cancel">Cancelar</button>
                <button type="button" class="app-alerts-btn app-alerts-btn-primary" data-role="ok">Aceptar</button>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);

    var modal = overlay.querySelector(".app-alerts-modal");
    var titleEl = overlay.querySelector(".app-alerts-title");
    var bodyEl = overlay.querySelector(".app-alerts-body");
    var cancelBtn = overlay.querySelector('[data-role="cancel"]');
    var okBtn = overlay.querySelector('[data-role="ok"]');
    var currentResolver = null;

    function close(result) {
        overlay.classList.remove("show");
        if (currentResolver) {
            var resolve = currentResolver;
            currentResolver = null;
            resolve(result);
        }
    }

    overlay.addEventListener("click", function (e) {
        if (e.target === overlay) close(false);
    });

    cancelBtn.addEventListener("click", function () { close(false); });
    okBtn.addEventListener("click", function () { close(true); });
    document.addEventListener("keydown", function (e) {
        if (!overlay.classList.contains("show")) return;
        if (e.key === "Escape") close(false);
    });

    function normalizeType(type) {
        var map = { error: "danger", danger: "danger", warning: "warning", success: "success", info: "info" };
        return map[type] || "info";
    }

    function show(opts) {
        opts = opts || {};
        var type = normalizeType(opts.type || "info");
        var isConfirm = !!opts.confirm;

        modal.className = "app-alerts-modal app-alerts-type-" + type;
        titleEl.textContent = opts.title || (isConfirm ? "Confirmar accion" : "Notificacion");
        bodyEl.textContent = opts.message || "";
        okBtn.textContent = opts.okText || "Aceptar";
        cancelBtn.textContent = opts.cancelText || "Cancelar";
        cancelBtn.style.display = isConfirm ? "inline-block" : "none";
        overlay.classList.add("show");
        okBtn.focus();

        return new Promise(function (resolve) {
            currentResolver = resolve;
        });
    }

    var api = {
        show: show,
        alert: function (message, options) {
            options = options || {};
            return show({
                type: options.type || "info",
                title: options.title || "Notificacion",
                message: message,
                okText: options.okText || "Aceptar",
                confirm: false
            });
        },
        confirm: function (message, options) {
            options = options || {};
            return show({
                type: options.type || "warning",
                title: options.title || "Confirmar accion",
                message: message,
                okText: options.okText || "Continuar",
                cancelText: options.cancelText || "Cancelar",
                confirm: true
            });
        }
    };

    window.appAlerts = api;

    document.addEventListener("click", async function (e) {
        var trigger = e.target.closest("[data-confirm-message]");
        if (!trigger) return;
        if (trigger.dataset.confirmBypassed === "1") {
            trigger.dataset.confirmBypassed = "0";
            return;
        }

        e.preventDefault();
        var message = trigger.getAttribute("data-confirm-message") || "Deseas continuar con esta accion?";
        var title = trigger.getAttribute("data-confirm-title") || "Confirmar accion";
        var okText = trigger.getAttribute("data-confirm-ok") || "Continuar";
        var cancelText = trigger.getAttribute("data-confirm-cancel") || "Cancelar";

        var accepted = await api.confirm(message, {
            title: title,
            okText: okText,
            cancelText: cancelText
        });

        if (!accepted) return;

        if (trigger.tagName === "A" && trigger.href) {
            window.location.href = trigger.href;
            return;
        }

        if (trigger.tagName === "BUTTON" && trigger.type === "submit" && trigger.form) {
            trigger.dataset.confirmBypassed = "1";
            if (typeof trigger.form.requestSubmit === "function") {
                trigger.form.requestSubmit(trigger);
            } else {
                trigger.form.submit();
            }
        }
    });
})();

