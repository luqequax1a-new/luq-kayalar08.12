import Errors from "../../../components/Errors";
import sehirler from "@modules/../sehirler.json";
import ilceler from "@modules/../ilceler.json";

Alpine.data(
    "Addresses",
    ({ initialAddresses, initialDefaultAddress, countries }) => ({
        addresses: initialAddresses,
        defaultAddress: initialDefaultAddress,
        countries,
        formOpen: false,
        editing: false,
        loading: false,
        form: { state: "" },
        states: {},
        districts: [],
        provincesTR: null,
        errors: new Errors(),

        get firstCountry() {
            return Object.keys(this.countries)[0];
        },

        get singleCountry() {
            return Object.keys(this.countries).length === 1;
        },

        get hasAddress() {
            return Object.keys(this.addresses).length !== 0;
        },

        get hasNoStates() {
            return Object.keys(this.states).length === 0;
        },

        init() {
            this.changeCountry(this.firstCountry);

            if (this.singleCountry) {
                this.form.country = this.firstCountry;
            }

            this.$watch("form.state", (newState) => {
                if (this.form.country === "TR" && this.provincesTR) {
                    const provinceName = this.states[newState];
                    const province = this.provincesTR.find(
                        (p) => p.name.toUpperCase() === provinceName.toUpperCase()
                    );
                    this.districts = province
                        ? province.districts.map((d) => d.name)
                        : [];
                    this.form.city = "";
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

            this.fetchStates(country, () => {
                if (
                    country === "TR" &&
                    this.singleCountry &&
                    Array.isArray(FleetCart.supportedLocales) &&
                    FleetCart.supportedLocales.length === 1
                ) {
                    const firstStateCode = Object.keys(this.states)[0];
                    if (firstStateCode) {
                        this.form.state = firstStateCode;
                        const provinceName = this.states[firstStateCode];
                        const districtsForProvince = ilceler
                            .filter(
                                (d) =>
                                    String(d.sehir_adi).toUpperCase() ===
                                    String(provinceName).toUpperCase()
                            )
                            .map((d) => d.ilce_adi);
                        this.districts = districtsForProvince;
                        if (this.districts.length) {
                            this.form.city = this.districts[0];
                        }
                    }
                }
            });

            if (country === "TR") {
                this.provincesTR = sehirler;
                this.districts = [];
            } else {
                this.provincesTR = null;
                this.districts = [];
                this.form.city = "";
            }
        },

        async fetchStates(country, callback) {
            const response = await axios.get(`/countries/${country}/states`);

            this.states = response.data;

            if (callback) {
                callback();
            }
        },

        edit(address) {
            this.formOpen = true;
            this.editing = true;

            this.$nextTick(() => {
                this.form = { ...address };

                this.fetchStates(address.country, () => {
                    this.form.state = "";

                    this.$nextTick(() => {
                        this.form.state = address.state;
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
            axios
                .put(`/account/addresses/${this.form.id}`, this.form)
                .then(({ data }) => {
                    this.formOpen = false;
                    this.editing = false;

                    this.addresses[this.form.id] = data.address;

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
            axios
                .post("/account/addresses", this.form)
                .then(({ data }) => {
                    this.formOpen = false;

                    let address = { [data.address.id]: data.address };

                    this.addresses = {
                        ...this.addresses,
                        ...address,
                    };

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

        resetForm() {
            this.form = { state: "" };
        },
    })
);
