import Errors from "../../../components/Errors";
import sehirler from "@modules/../sehirler.json";
import ilceler from "@modules/../ilceler.json";

function trTitleCase(name) {
    if (name === null || name === undefined) return name;
    const mapUpperToLower = { I: "ı", İ: "i", Ç: "ç", Ş: "ş", Ğ: "ğ", Ü: "ü", Ö: "ö" };
    let s = String(name);
    s = s.replace(/[IİÇŞĞÜÖ]/g, (ch) => mapUpperToLower[ch] || ch);
    s = s.toLowerCase();
    const mapLowerToUpper = { i: "İ", ı: "I", ç: "Ç", ş: "Ş", ğ: "Ğ", ü: "Ü", ö: "Ö" };
    return s
        .split(/([\s\-]+)/)
        .map((part, idx) => {
            if (idx % 2 === 1) return part;
            if (!part) return part;
            const first = part.charAt(0);
            const rest = part.slice(1);
            const firstU = mapLowerToUpper[first] || first.toUpperCase();
            return firstU + rest;
        })
        .join("");
}

const SEHIRLER = sehirler.map((s) => ({ ...s, sehir_adi: trTitleCase(s.sehir_adi) }));
const ILCELER = ilceler.map((d) => ({ ...d, sehir_adi: trTitleCase(d.sehir_adi), ilce_adi: trTitleCase(d.ilce_adi) }));

Alpine.data(
    "Addresses",
    ({ initialAddresses, initialDefaultAddress, countries }) => ({
        addresses: initialAddresses,
        defaultAddress: initialDefaultAddress,
        countries,
        formOpen: false,
        editing: false,
        loading: false,
        form: {},
        states: {},
        districts: [],
        errors: new Errors(),

        get firstCountry() {
            const keys = Object.keys(this.countries);
            return keys[0];
        },

        get singleCountry() {
            return Object.keys(this.countries).length === 1;
        },

        get hasAddress() {
            return Object.keys(this.addresses).length !== 0;
        },

        init() {
            this.changeCountry(this.firstCountry);

            if (this.singleCountry) {
                this.form.country = this.firstCountry;
            }

            this.$watch("form.state", (newState) => {
                if (this.form.country === "TR") {
                    const provinceName = this.states[newState];
                    const key = this.normalizeTR(provinceName);
                    this.districts = ILCELER
                        .filter((d) => this.normalizeTR(d.sehir_adi) === key)
                        .map((d) => d.ilce_adi);
                    if (!this.districts.includes(this.form.city)) {
                        this.form.city = "";
                    }
                } else {
                    this.districts = [];
                }
            });
        },

        changeDefaultAddress(address) {
            if (this.defaultAddress.address_id === address.id) return;

            this.defaultAddress.address_id = address.id;

            axios
                .post("/account/addresses/change-default", {
                    address_id: address.id,
                })
                .then((response) => {
                    notify(response.data);
                })
                .catch((error) => {
                    notify(error.response.data.message);
                });
        },

        changeCountry(country) {
            this.form.country = country;
            this.form.state = "";

            this.fetchStates(country, (states) => {
                this.states = states;
                if (country !== "TR") {
                    this.districts = [];
                } else {
                    this.districts = [];
                }
            });
        },

        normalizeTR(s) {
            if (!s) return s;
            const map = { İ: 'I', ı: 'I', i: 'I', I: 'I', Ç: 'C', ç: 'c', Ş: 'S', ş: 's', Ğ: 'G', ğ: 'g', Ü: 'U', ü: 'u', Ö: 'O', ö: 'o' };
            const t = String(s)
                .replace(/[İıiIÇçŞşĞğÜüÖö]/g, (m) => map[m])
                .normalize('NFD').replace(/\p{Diacritic}/gu, '')
                .toUpperCase();
            return t;
        },

        async fetchStates(country, callback) {
            const response = await axios.get(`/countries/${country}/states`);

            if (callback) {
                callback(response.data);
            }
        },

        changeCity(city) {
            this.form.city = city;
            if (this.form.country === "TR" && Array.isArray(this.districts) && this.districts.length) {
                const district = ILCELER.find((d) => String(d.ilce_adi) === String(city));
                if (district) {
                    const key = this.normalizeTR(district.sehir_adi);
                    const code = Object.keys(this.states || {}).find((c) => this.normalizeTR(this.states[c]) === key);
                    if (code) {
                        this.form.state = code;
                    }
                }
            }
        },

        edit(address) {
            this.formOpen = true;
            this.editing = true;

            this.$nextTick(() => {
                this.form = { ...address };

                this.fetchStates(address.country, (states) => {
                    this.states = states;
                    this.form.state = "";

                    this.$nextTick(() => {
                        this.form.state = address.state;
                        if (address.country === "TR") {
                            const provinceName = this.states[this.form.state];
                            const key = this.normalizeTR(provinceName);
                            this.districts = ILCELER
                                .filter((d) => this.normalizeTR(d.sehir_adi) === key)
                                .map((d) => d.ilce_adi);
                        } else {
                            this.districts = [];
                        }
                    });
                });
            });
        },

        remove(address) {
            if (!confirm(trans("storefront::account.addresses.confirm"))) {
                return;
            }

            axios
                .delete(`/account/addresses/${address.id}`)
                .then((response) => {
                    delete this.addresses[address.id];

                    notify(response.data.message);
                })
                .catch((error) => {
                    notify(error.response.data.message);
                });
        },

        cancel() {
            this.editing = false;
            this.formOpen = false;

            this.errors.reset();
            this.resetForm();
        },

        save() {
            this.loading = true;

            this.editing ? this.update() : this.create();
        },

        update() {
            const payload = { ...this.form };

            axios
                .put(`/account/addresses/${payload.id}`, payload)
                .then(({ data }) => {
                    this.formOpen = false;
                    this.editing = false;

                    this.addresses[payload.id] = data.address;

                    this.resetForm();

                    notify(data.message);
                })
                .catch(({ response }) => {
                    if (response.status === 422) {
                        this.errors.record(response.data.errors);
                    }

                    notify(response.data.message);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        create() {
            const payload = { ...this.form };

            axios
                .post('/account/addresses', payload)
                .then(({ data }) => {
                    this.formOpen = false;
                    this.addresses = { ...this.addresses, [data.address.id]: data.address };
                    this.resetForm();
                    notify(trans('account::messages.address_created'));
                })
                .catch(({ response }) => {
                    if (response?.status === 422) {
                        this.errors.record(response.data.errors);
                    }
                    notify(response?.data?.message || trans('storefront::storefront.something_went_wrong'));
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        resetForm() {
            this.form = {};
        },
    })
);
