export async function uploadMediaFiles(files) {
    const list = Array.from(files || []);

    const requests = list.map((file) => {
        const formData = new FormData();
        formData.append("file", file);

        const http = window.axios || null;

        if (!http) {
            return Promise.reject(new Error("axios is not available"));
        }

        return http.post("/media", formData, {
            headers: { "Content-Type": "multipart/form-data" },
        }).then((response) => {
            const record = response.data || {};

            return {
                id: record.id,
                path: record.path_url || record.path || "",
                poster: null,
                type: record.mime || "",
            };
        });
    });

    return Promise.all(requests);
}
