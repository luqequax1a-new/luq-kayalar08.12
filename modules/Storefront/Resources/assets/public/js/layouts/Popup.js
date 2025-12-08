import Alpine from "alpinejs";

function getNow() {
    return Math.floor(Date.now() / 1000);
}

function getStorageKey(id) {
    return `fc_popup_shown_${id}`;
}

function canShow(config) {
    if (!config || !config.id) return false;

    if (config.frequency_type === "always") {
        return true;
    }

    const key = getStorageKey(config.id);

    if (config.frequency_type === "per_session") {
        if (window.sessionStorage.getItem(key)) {
            return false;
        }

        return true;
    }

    if (config.frequency_type === "per_days") {
        try {
            const raw = window.localStorage.getItem(key);
            if (!raw) return true;

            const last = parseInt(raw, 10) || 0;
            const diffSeconds = getNow() - last;
            const days = config.frequency_value || 1;

            return diffSeconds >= days * 24 * 60 * 60;
        } catch (e) {
            return true;
        }
    }

    if (config.frequency_type === "per_hours") {
        try {
            const raw = window.localStorage.getItem(key);
            if (!raw) return true;

            const last = parseInt(raw, 10) || 0;
            const diffSeconds = getNow() - last;
            const hours = config.frequency_value || 1;

            return diffSeconds >= hours * 60 * 60;
        } catch (e) {
            return true;
        }
    }

    return true;
}

function markShown(config) {
    if (!config || !config.id) return;

    const key = getStorageKey(config.id);

    if (config.frequency_type === "per_session") {
        try {
            window.sessionStorage.setItem(key, String(getNow()));
        } catch (e) {}
        return;
    }

    if (config.frequency_type === "per_days") {
        try {
            window.localStorage.setItem(key, String(getNow()));
        } catch (e) {}
        return;
    }

    if (config.frequency_type === "per_hours") {
        try {
            window.localStorage.setItem(key, String(getNow()));
        } catch (e) {}
        return;
    }
}

function setupExitIntent(callback) {
    function handler(e) {
        if (e.clientY <= 0) {
            window.removeEventListener("mouseout", handler);
            callback();
        }
    }

    window.addEventListener("mouseout", handler);
}

document.addEventListener("alpine:init", () => {
    Alpine.data("MarketingPopup", (config) => ({
        isOpen: false,

        init() {
            if (!canShow(config)) {
                return;
            }

            const type = config.trigger_type;

            if (type === "on_load_delay") {
                const delay = (config.trigger_value || 3) * 1000;
                setTimeout(() => {
                    if (!this.isOpen && canShow(config)) {
                        this.open();
                    }
                }, delay);
            } else if (type === "exit_intent") {
                setupExitIntent(() => {
                    if (!this.isOpen && canShow(config)) {
                        this.open();
                    }
                });
            }
        },

        open() {
            this.isOpen = true;
            markShown(config);
        },

        close() {
            this.isOpen = false;
        },
    }));
});
