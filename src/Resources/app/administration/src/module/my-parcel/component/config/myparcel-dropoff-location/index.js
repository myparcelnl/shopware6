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
        }
    },
    mounted() {
        this.check();
        window.addEventListener('focus', ()=>{this.check()});
        if (this.value){
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
            this.isLoading = true;
            this.myParcelDropOffService.getDropOffLocation({apiKey: this.pluginConfig['MyPaShopware.config.myParcelApiKey']}).then((res) => {
                this._parseResult(res);
                this.isLoading = false;
            });
        },
        _parseResult(result) {
            //Check if error
            if (result['errorMessage']) {
                this.createNotificationSuccess({
                    title: this.$tc('global.default.warning'),
                    message: this.$tc('sw-myparcel.config.instaboxSelector.somethingWrongMessage')
                });
                return;
            }
            this.newDropOffPoint = result;
            //If there was no previous point make this the selected one
            if (this.loadedDropOffPoint == null) {
                this.loadedDropOffPoint=result;
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
