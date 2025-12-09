import tinyMCE from "@admin/js/wysiwyg";

// Initialize TinyMCE for any .wysiwyg textarea on dynamic category admin pages
export default function initDynamicCategoryWysiwyg() {
    tinyMCE();
}

// Auto-run when this entry is loaded via Vite
initDynamicCategoryWysiwyg();
