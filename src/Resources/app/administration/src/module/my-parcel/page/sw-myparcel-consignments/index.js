import template from './sw-myparcel-consignments.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

const PACKAGE_TYPE_PACKAGE_ID = 1;
const PACKAGE_TYPE_MAILBOX_ID = 2;
const PACKAGE_TYPE_LETTER_ID = 3;
const PACKAGE_TYPE_DIGITAL_STAMP_ID = 4;

const PACKAGE_TYPE_PACKAGE_SNIPPET = 'sw-myparcel.general.packageTypes.package';
const PACKAGE_TYPE_MAILBOX_SNIPPET = 'sw-myparcel.general.packageTypes.mailbox';
const PACKAGE_TYPE_LETTER_SNIPPET = 'sw-myparcel.general.packageTypes.letter';
const PACKAGE_TYPE_DIGITAL_STAMP_SNIPPET = 'sw-myparcel.general.packageTypes.digitalStamp';

const CARRIER_POSTNL_ID = 1;
const CARRIER_BPOST_ID = 2;
const CARRIER_DPD_ID = 3;

const CARRIER_POSTNL_SNIPPET = 'sw-myparcel.general.carriers.postNL';
const CARRIER_BPOST_SNIPPET = 'sw-myparcel.general.carriers.bpost';
const CARRIER_DPD_SNIPPET = 'sw-myparcel.general.carriers.dpd';

Component.register('sw-myparcel-consignments', {
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
            consignments: [],
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            packageTypes: {
                [PACKAGE_TYPE_PACKAGE_ID]: this.$tc(PACKAGE_TYPE_PACKAGE_SNIPPET),
                [PACKAGE_TYPE_MAILBOX_ID]: this.$tc(PACKAGE_TYPE_MAILBOX_SNIPPET),
                [PACKAGE_TYPE_LETTER_ID]: this.$tc(PACKAGE_TYPE_LETTER_SNIPPET),
                [PACKAGE_TYPE_DIGITAL_STAMP_ID]: this.$tc(PACKAGE_TYPE_DIGITAL_STAMP_SNIPPET)
            },
            carriers: {
                [CARRIER_POSTNL_ID]: this.$tc(CARRIER_POSTNL_SNIPPET),
                [CARRIER_BPOST_ID]: this.$tc(CARRIER_BPOST_SNIPPET),
                [CARRIER_DPD_ID]: this.$tc(CARRIER_DPD_SNIPPET)
            }
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
        consignmentColumns() {
            return this.getConsignmentColumns();
        },

        consignmentRepository() {
            return this.repositoryFactory.create('kiener_my_parcel_shipment');
        },

        consignmentCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addAssociation('order');
            criteria.addAssociation('shippingOption');

            return criteria;
        },
    },

    methods: {
        getConsignmentColumns() {
            return [{
                property: 'createdAt',
                label: 'sw-myparcel.columns.consignmentDateColumn',
                allowResize: true,
                primary: true
            }, {
                property: 'order.orderNumber',
                label: 'sw-myparcel.columns.orderColumn',
                routerLink: 'sw.order.detail',
                allowResize: true
            }, {
                property: 'order.orderDateTime',
                label: 'sw-myparcel.columns.orderDateColumn',
                allowResize: true
            }, {
                property: 'shippingOption.packageType',
                label: 'sw-myparcel.columns.packageTypeColumn',
                allowResize: true
            }, {
                property: 'shippingOption.carrierId',
                label: 'sw-myparcel.columns.carrierColumn',
                allowResize: true
            }];
        },

        getList() {
            this.isLoading = true;

            return this.consignmentRepository.search(this.consignmentCriteria, Shopware.Context.api).then((response) => {
                this.total = response.total;
                this.consignments = response;
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
                document.location = item.labelUrl;
            }
        }
    }
});