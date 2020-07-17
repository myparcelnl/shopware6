import template from './sw-myparcel-orders.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

const DELIVERY_TYPE_MORNING = 1;
const DELIVERY_TYPE_EVENING = 3;

const ACTION_TYPE_DOWNLOAD = 'download';
const ACTION_TYPE_CREATE = 'create';

Component.register('sw-myparcel-orders', {
    template: template,

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification')
    ],

    inject: [
        'repositoryFactory',
        'stateStyleDataProviderService',
        'MyParcelConsignmentService'
    ],

    data() {
        return {
            isLoading: false,
            filterLoading: false,
            availableAffiliateCodes: [],
            affiliateCodeFilter: [],
            availableCampaignCodes: [],
            campaignCodeFilter: [],
            shippingOptions: [],
            sortBy: 'order.orderDateTime',
            sortDirection: 'ASC',
            createSingleConsignmentLoading: false,
            createMultipleConsignmentsLoading: false,
            createSingleConsignment: {
                item: null,
                actionType: ACTION_TYPE_DOWNLOAD,
                printPosition: [1,2,3,4],
                numberOfLabels: 1,
                showModal: false,
            },
            createMultipleConsignments: {
                items: [],
                actionType: ACTION_TYPE_DOWNLOAD,
                packageType: 1,
                printPosition: [1,2,3,4],
                showModal: false,
            },
            selectionCount: 0,
            selectedShippingOptions: null,
            selectedShippingOptionIds: [],
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
        orderColumns() {
            return this.getOrderColumns();
        },

        createMultipleConsignmentsAvailable() {
            return !!this.selectedShippingOptionIds && this.selectionCount > 0 || false;
        },

        shippingOptionRepository() {
            return this.repositoryFactory.create('kiener_my_parcel_shipping_option');
        },

        shippingOptionCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);

            if (this.affiliateCodeFilter.length > 0) {
                criteria.addFilter(Criteria.equalsAny('affiliateCode', this.affiliateCodeFilter));
            }
            if (this.campaignCodeFilter.length > 0) {
                criteria.addFilter(Criteria.equalsAny('campaignCode', this.campaignCodeFilter));
            }

            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addAssociation('order');
            criteria.addAssociation('order.addresses');
            criteria.addAssociation('order.salesChannel');
            criteria.addAssociation('order.orderCustomer');
            criteria.addAssociation('order.currency');
            criteria.addAssociation('order.transactions');
            criteria.addAssociation('order.deliveries');

            return criteria;
        },
    },

    methods: {
        createdComponent() {
            this.loadFilterValues();
        },

        getOrderColumns() {
            return [{
                property: 'order.orderNumber',
                label: 'sw-order.list.columnOrderNumber',
                routerLink: 'sw.order.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'order.salesChannel.name',
                label: 'sw-order.list.columnSalesChannel',
                allowResize: true
            }, {
                property: 'order.orderCustomer.firstName',
                dataIndex: 'orderCustomer.firstName,orderCustomer.lastName',
                label: 'sw-order.list.columnCustomerName',
                allowResize: true
            }, {
                property: 'order.billingAddressId',
                label: 'sw-order.list.columnBillingAddress',
                allowResize: true
            }, {
                property: 'order.amountTotal',
                label: 'sw-order.list.columnAmount',
                align: 'right',
                allowResize: true
            }, {
                property: 'order.stateMachineState.name',
                label: 'sw-order.list.columnState',
                allowResize: true
            }, {
                property: 'order.transactions[0].stateMachineState.name',
                label: 'sw-order.list.columnTransactionState',
                allowResize: true
            }, {
                property: 'order.deliveries[0].stateMachineState.name',
                label: 'sw-order.list.columnDeliveryState',
                allowResize: true
            }, {
                property: 'order.orderDateTime',
                label: 'sw-order.list.orderDate',
                allowResize: true
            }, {
                property: 'order.affiliateCode',
                inlineEdit: 'string',
                label: 'sw-order.list.columnAffiliateCode',
                allowResize: true,
                visible: false
            }, {
                property: 'order.campaignCode',
                inlineEdit: 'string',
                label: 'sw-order.list.columnCampaignCode',
                allowResize: true,
                visible: false
            }];
        },

        getBillingAddress(order) {
            return order.addresses.find((address) => {
                return address.id === order.billingAddressId;
            });
        },

        getVariantFromOrderState(order) {
            return this.stateStyleDataProviderService.getStyle(
                'order.state', order.stateMachineState.technicalName
            ).variant;
        },

        getVariantFromPaymentState(order) {
            return this.stateStyleDataProviderService.getStyle(
                'order_transaction.state', order.transactions[0].stateMachineState.technicalName
            ).variant;
        },

        getVariantFromDeliveryState(order) {
            return this.stateStyleDataProviderService.getStyle(
                'order_delivery.state', order.deliveries[0].stateMachineState.technicalName
            ).variant;
        },

        loadFilterValues() {
            this.filterLoading = true;

            return this.orderRepository.search(this.filterSelectCriteria, Shopware.Context.api).then(({ aggregations }) => {
                this.availableAffiliateCodes = aggregations.affiliateCodes.buckets;
                this.availableCampaignCodes = aggregations.campaignCodes.buckets;
                this.filterLoading = false;

                return aggregations;
            }).catch(() => {
                this.filterLoading = false;
            });
        },

        onChangeAffiliateCodeFilter(value) {
            this.affiliateCodeFilter = value;
            this.getList();
        },

        onChangeCampaignCodeFilter(value) {
            this.campaignCodeFilter = value;
            this.getList();
        },

        closeModals() {
            this.closeSingleConsignmentModal();
            this.closeMultipleConsignmentsModal();
        },

        closeSingleConsignmentModal() {
            this.createSingleConsignment.showModal = false;
            this.createSingleConsignmentLoading = false;
        },

        closeMultipleConsignmentsModal() {
            this.createMultipleConsignments.showModal = false;
            this.createMultipleConsignmentsLoading = false;
        },

        saveSingleConsignment(consignmentData) {
            this.createSingleConsignmentLoading = true;

            let order = {
                order_id: consignmentData.item.order.id,
                order_version_id: consignmentData.item.order.versionId,
                shipping_option_id: consignmentData.item.id,
                package_type: consignmentData.item.packageType,
            };

            consignmentData.packageType = consignmentData.item.packageType;

            this.createConsignments([order], consignmentData);
        },

        saveMultipleConsignments(consignmentData) {
            this.createMultipleConsignmentsLoading = true;

            let orders = [];

            for (let id in consignmentData.items) {
                let item = null;

                if (!!consignmentData.items[id]) {
                    item = consignmentData.items[id];
                }

                if (!!item) {
                    orders.push({
                        order_id: item.orderId,
                        order_version_id: item.orderVersionId,
                        shipping_option_id: id,
                        package_type: consignmentData.packageType,
                    });
                }
            }

            if (orders.length) {
                this.createConsignments(orders, consignmentData);
            }
        },

        createConsignments(orders, consignmentData) {
            this.MyParcelConsignmentService.createConsignments({
                orders: orders,
                label_positions: consignmentData.printPosition,
                package_type: consignmentData.packageType
            })
                .then((response) => {
                    if (response.success === true) {
                        this.createNotificationSuccess({
                            title: this.$tc('sw-myparcel.general.mainMenuItemGeneral'),
                            message: this.$tc('sw-myparcel.messages.consignmentSuccess')
                        });

                        if (
                            !!response.labelUrl
                            && consignmentData.actionType === ACTION_TYPE_DOWNLOAD
                        ) {
                            document.location = response.labelUrl;
                        }
                    } else {
                        this.createNotificationError({
                            title: this.$tc('sw-myparcel.general.mainMenuItemGeneral'),
                            message: this.$tc('sw-myparcel.messages.error')
                        });
                    }

                    this.closeModals();
                })
                .catch(() => {
                    this.createNotificationError({
                        title: this.$tc('sw-myparcel.general.mainMenuItemGeneral'),
                        message: this.$tc('sw-myparcel.messages.error')
                    });

                    this.closeModals();
                });
        },

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

        onSelectionChanged(selected, selectionCount) {
            this.selectionCount = selectionCount;
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

        onOpenCreateSingleConsignmentModal(item) {
            this.createSingleConsignment.item = item;
            this.selectedShippingOptionIds = [item.id];
            this.createSingleConsignment.showModal = true;
        },

        onCloseSingleConsignmentModal() {
            this.createSingleConsignment.showModal = false;
        },

        onCreateSingleConsignment() {
            if (!!this.createSingleConsignment.item) {
                this.shippingOptionRepository.save(this.createSingleConsignment.item, Shopware.Context.api)
                    .then(() => {
                        this.saveSingleConsignment(this.createSingleConsignment);
                    })
                    .catch(() => {
                        //
                    });
            }
        },

        onOpenCreateMultipleConsignmentsModal() {
            if (
                !!this.selectedShippingOptionIds
                && this.selectedShippingOptionIds.length
            ) {
                this.createMultipleConsignments.items = this.selectedShippingOptions;
                this.createMultipleConsignments.showModal = true;
            }
        },

        onCloseMultipleConsignmentsModal() {
            this.createMultipleConsignments.showModal = false;
        },

        onCreateMultipleConsignments() {
            this.saveMultipleConsignments(this.createMultipleConsignments);
        },
    }
});