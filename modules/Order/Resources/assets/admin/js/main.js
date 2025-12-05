$("#order-status").on("change", (e) => {
    axios
        .put(`/orders/${e.currentTarget.dataset.id}/status`, {
            status: e.currentTarget.value,
        })
        .then((response) => {
            success(response.data);
        })
        .catch(({response}) => {
            error(response.data.message);
        });
});

import GLightbox from "glightbox";
import "glightbox/dist/css/glightbox.css";

document.addEventListener("DOMContentLoaded", () => {
    GLightbox({
        selector: ".glightbox",
        touchNavigation: true,
        loop: false,
        closeOnOutsideClick: true,
    });
});
