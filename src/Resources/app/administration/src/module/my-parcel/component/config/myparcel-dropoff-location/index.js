import template from "./myparcel-dropoff-location.html.twig";
import './myparcel-dropoff-location.scss';

const {Component, Mixin} = Shopware;


Component.register('myparcel-dropoff-location', {
    template,

    inject: [
        'myParcelDropOffService'
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            isLoading: false,
            loadedDropOffPoint: null,
            newDropOffPoint: null,
            hasBeenSaved: true,
            hasShownDropOffError: false,
            showAddressBar: false
        }
    },
    mounted() {
        this.check();
        window.addEventListener('focus', () => {
            this.check()
        });
        if (this.value) {
            this.loadedDropOffPoint = JSON.parse(this.value);
        }
    },
    props: {
        value: {
            required: true,
        },
    },
    computed: {
        selectedDropOffPoint() {
            //Convert json to object
            if (this.value) {
                return JSON.parse(this.value)
            }
            return {}
        },
        hasNewDropOffPoint() {
            if (this.newDropOffPoint == null) {
                return false;
            }
            return JSON.stringify(this.loadedDropOffPoint) !== JSON.stringify(this.newDropOffPoint);
        },
        pluginConfig() {
            let $parent = this.$parent;
            while ($parent.actualConfigData === undefined) {
                $parent = $parent.$parent;
            }
            return $parent.actualConfigData.null;
        }
    },

    methods: {
        check() {
            //Is there any api key set?
            if (!this.pluginConfig['MyPaShopware.config.myParcelApiKey']) {
                return;
            }
            //Is instabox set and enabled?
            if (!this.pluginConfig['MyPaShopware.config.enabledInstabox']) {
                return;
            }

            this.isLoading = true;
            this.myParcelDropOffService.getDropOffLocation({apiKey: this.pluginConfig['MyPaShopware.config.myParcelApiKey']})
                .then((result) => {
                    this.showAddressBar = true;
                    this._parseResult(result);
                    this.isLoading = false;
                })
                .catch((result) => {
                    this.isLoading = false;
                    //Check if error
                    if (result['errorMessage']) {
                        if (result['errorMessage'] === 'error') {
                            this.createNotificationWarning({
                                title: this.$tc('global.default.warning'),
                                message: this.$tc('sw-myparcel.config.instaboxSelector.somethingWrongMessage')
                            });
                        }
                        if (result['errorMessage'] === 'dropOff' && !this.hasShownDropOffError) {
                            this.createNotificationWarning({
                                title: this.$tc('global.default.warning'),
                                message: this.$tc('sw-myparcel.config.instaboxSelector.dropOffWrongMessage')
                            });
                            this.hasShownDropOffError = true;
                        }
                    }
                });
        },
        _parseResult(result) {
            this.newDropOffPoint = result;
            //If there was no previous point make this the selected one
            if (this.loadedDropOffPoint == null) {
                this.loadedDropOffPoint = result;
                this._saveDropOffLocation(result);
                this.hasBeenSaved = false;
            }
        },
        _saveDropOffLocation(value) {
            this.$emit('input', JSON.stringify(value));
        },
        dropOffLocationSelected(locationCode) {
            if (this.newDropOffPoint.locationCode === locationCode) {
                this._saveDropOffLocation(this.newDropOffPoint);
                this.hasBeenSaved = false;
            } else {
                this._saveDropOffLocation(this.loadedDropOffPoint);
            }
        }
    }

});
