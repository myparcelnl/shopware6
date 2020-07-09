import template from './sw-myparcel-orders.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

const DELIVERY_TYPE_MORNING = 1;
const DELIVERY_TYPE_EVENING = 3;

Component.register('sw-myparcel-orders', {
    template: template,

    mixins: [
        Mixin.getByName('listing')
    ],

    inject: [
        'repositoryFactory',
        'MyParcelShippingOptionService'
    ],

    data() {
        return {
            isLoading: false,
            shippingOptions: [],
            sortBy: 'createdAt',
            sortDirection: 'ASC',
            createSingleShipment: {
                item: null,
                actionType: 'download',
                printPosition: [1,2,3,4],
                numberOfLabels: 1,
                showModal: false,
            },
            createMultipleShipments: {
                items: [],
                actionType: 'download',
                printPosition: [1,2,3,4],
                showModal: false,
            },
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    mounted() {
        this.getList();
    },

    computed: {
        shippingOptionRepository() {
            return this.repositoryFactory.create('kiener_my_parcel_shipping_option');
        },

        shippingOptionCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addAssociation('order');
            criteria.addAssociation('shipment');

            return criteria;
        },
    },

    methods: {
        getList() {
            this.isLoading = true;

            return this.shippingOptionRepository.search(this.shippingOptionCriteria, Shopware.Context.api).then((response) => {
                this.total = response.total;
                this.shippingOptions = response;
                this.isLoading = false;

                return response;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        onlyRecipientChecked(item) {
            return this.onlyRecipientDisabled(item) === true
                || item.onlyRecipient;
        },

        onlyRecipientDisabled(item) {
            return item.deliveryType === DELIVERY_TYPE_MORNING
                || item.deliveryType === DELIVERY_TYPE_EVENING;
        },

        onSelectionChanged(selected) {
            console.log(selected);
        },

        onSortColumn(column) {
            let sortDirection = this.sortDirection;
            sortDirection = sortDirection === 'ASC' ? 'DESC' : 'ASC';

            if (this.sortBy !== column.dataIndex) {
                sortDirection = 'ASC';
            }

            this.sortBy = column.dataIndex;
            this.sortDirection = sortDirection;
            this.getList();
        },

        onOpenCreateSingleShipmentModal(item) {
            this.createSingleShipment.item = item;
            this.createSingleShipment.showModal = true;
        },

        onCloseSingleShipmentModal() {
            this.createSingleShipment.showModal = false;
        },

        onCreateSingleShipment() {
            console.log(this.createSingleShipment.item);
        },

        onOpenCreateMultipleShipmentsModal(item) {
            this.createMultipleShipments.item = item;
            this.createMultipleShipments.showModal = true;
        },

        onCloseMultipleShipmentsModal() {
            this.createMultipleShipments.showModal = false;
        },

        onCreateMultipleShipments() {
            console.log(this.createMultipleShipments.item);
        },
    }
});