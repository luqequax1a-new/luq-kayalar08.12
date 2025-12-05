export function trans(langKey, replace = {}) {
    let line = window.FleetCart.langs[langKey] ?? "";

    const keys = Object.keys(replace).sort((a, b) => b.length - a.length);
    for (const key of keys) {
        const value = replace[key];
        const re = new RegExp(`:${key}(?![A-Za-z_])`, "g");
        line = line.replace(re, value);
    }

    return line;
}

export function formatCurrency(amount) {
    const formatted = new Intl.NumberFormat(FleetCart.locale.replace("_", "-"), {
        ...(FleetCart.locale === "ar" && {
            numberingSystem: "arab",
        }),
        style: "currency",
        currency: FleetCart.currency,
        currencyDisplay: "symbol",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);

    return formatted.replace("TRY", "₺");
}

export function generateUid() {
    const timestamp = Math.floor(Math.random() * Date.now()).toString(36);
    const randomPart = Math.random().toString(36).substring(2, 8);

    return (timestamp + randomPart).substring(0, 12);
}
