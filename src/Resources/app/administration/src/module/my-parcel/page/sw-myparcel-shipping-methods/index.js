import template from './sw-myparcel-shipping-methods.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-myparcel-shipping-methods', {
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
            shippingMethods: [],
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
        shippingMethodRepository() {
            return this.repositoryFactory.create('kiener_my_parcel_shipping_method');
        },

        shippingMethodCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            return criteria;
        },
    },

    methods: {
        getList() {
            this.isLoading = true;

            return this.shippingMethodRepository.search(this.shippingMethodCriteria, Shopware.Context.api).then((response) => {
                this.total = response.total;
                this.shippingMethods = response;
                this.isLoading = false;

                return response;
            }).catch(() => {
                this.isLoading = false;
            });
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
    }
});