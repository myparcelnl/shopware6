import template from './sw-myparcel-shipments.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-myparcel-shipments', {
    template: template,

    mixins: [
        Mixin.getByName('listing')
    ],

    inject: [
        'repositoryFactory',
    ],

    data() {
        return {
            isLoading: false,
            shipments: [],
            sortBy: 'createdAt',
            sortDirection: 'ASC',
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
        shipmentRepository() {
            return this.repositoryFactory.create('kiener_my_parcel_shipment');
        },

        shipmentCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addAssociation('order');
            criteria.addAssociation('shippingOption');

            return criteria;
        },
    },

    methods: {
        getList() {
            this.isLoading = true;

            return this.shipmentRepository.search(this.shipmentCriteria, Shopware.Context.api).then((response) => {
                this.total = response.total;
                this.shipments = response;
                this.isLoading = false;

                return response;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        onSelectionChanged(selected) {
            this.selectedShippingOptions = selected;
            this.selectedShippingOptionIds = [];

            if (!!this.selectedShippingOptions) {
                for (let id in this.selectedShippingOptions) {
                    this.selectedShippingOptionIds.push(id);
                }
            }
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

        onDownloadLabel(item) {
            if (!!item.labelUrl) {
                window.open(item.labelUrl);
            }
        }
    }
});