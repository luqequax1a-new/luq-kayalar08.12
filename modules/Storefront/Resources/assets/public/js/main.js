// stores
import "./app";
import "./stores/cartStore";
import "./stores/wishlistStore";
import "./stores/compareStore";
import "./stores/layoutStore";

// layouts
import "./layouts/Header";
import "./layouts/PrimaryMenu";
import "./layouts/SidebarCart";
import "./layouts/CookieBar";
import "./layouts/NewsletterSubscription";
import "./layouts/NewsletterPopup";
import "./layouts/Popup";
import "./layouts/ScrollToTop";

document.addEventListener("DOMContentLoaded", () => {
    if (window.Alpine && typeof window.Alpine.start === "function") {
        window.Alpine.start();
    }
});
