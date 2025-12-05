import axios from "axios";

export default function registerCartUpsellBox(Alpine) {
    Alpine.data("CartUpsellBox", ({ offer, addUpsellUrl }) => ({
        offer,
        addUpsellUrl,
        show: true,
        adding: false,
        remainingSeconds: offer.countdown_seconds || null,
        countdownTimer: null,
        countdownEndAt: null,

        init() {
            if (this.remainingSeconds && this.remainingSeconds > 0) {
                // Gerçek zaman bazlı bitiş anını hesapla
                this.countdownEndAt = Date.now() + this.remainingSeconds * 1000;

                this.countdownTimer = setInterval(() => {
                    if (this.countdownEndAt) {
                        const diffMs = this.countdownEndAt - Date.now();
                        const nextRemaining = Math.max(0, Math.round(diffMs / 1000));
                        this.remainingSeconds = nextRemaining;
                    }

                    if (!this.showCountdown) {
                        if (this.countdownTimer) {
                            clearInterval(this.countdownTimer);
                        }

                        this.show = false;
                    }
                }, 1000);
            }
        },

        get showCountdown() {
            return this.remainingSeconds !== null && this.remainingSeconds > 0;
        },

        get countdownLabel() {
            if (!this.showCountdown) {
                return "";
            }

            const minutes = Math.floor(this.remainingSeconds / 60)
                .toString()
                .padStart(2, "0");
            const seconds = (this.remainingSeconds % 60)
                .toString()
                .padStart(2, "0");

            return `${minutes}:${seconds}`;
        },

        get dismissKey() {
            return `fc_upsell_rule_${this.offer.rule_id}_dismissed`;
        },

        reject() {
            this.show = false;
        },

        async add() {
            if (this.adding) return;

            this.adding = true;

            try {
                const payload = {
                    rule_id: this.offer.rule_id,
                    product_id: this.offer.product_id,
                    variant_id: this.offer.preselected_variant_id || null,
                    qty: 1,
                };

                await axios.post(this.addUpsellUrl, payload);

                if (window.location && typeof window.location.reload === "function") {
                    window.location.reload();
                }
            } catch (error) {
                try { console.error("Upsell add failed", error); } catch (_) {}
            } finally {
                this.adding = false;
            }
        },
    }));
}
