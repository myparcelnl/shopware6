import template from './sw-myparcel-orders.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

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
                actionType: null,
                showModal: false,
            },
            createMultipleShipment: {
                items: [],
                actionType: null,
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
    }
});