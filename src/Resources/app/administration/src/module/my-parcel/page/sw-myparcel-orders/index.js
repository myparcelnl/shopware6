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
        'MyParcelShipmentService',
        'MyParcelConsignmentService',
    ],

    data() {
        return {
            isLoading: false,
            shippingOptions: [],
            sortBy: 'createdAt',
            sortDirection: 'ASC',
            createSingleShipment: {
                item: null,
                actionType: ACTION_TYPE_DOWNLOAD,
                printPosition: [1,2,3,4],
                numberOfLabels: 1,
                showModal: false,
            },
            createMultipleShipments: {
                items: [],
                actionType: ACTION_TYPE_DOWNLOAD,
                packageType: 1,
                printPosition: [1,2,3,4],
                showModal: false,
            },
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
        createMultipleShipmentsAvailable() {
            return !!this.selectedShippingOptionIds && this.selectedShippingOptionIds.length > 0 || false;
        },

        shippingOptionRepository() {
            return this.repositoryFactory.create('kiener_my_parcel_shipping_option');
        },

        shippingOptionCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));
            criteria.addAssociation('order');

            return criteria;
        },
    },

    methods: {
        saveSingleShipment(shipmentData) {
            this.MyParcelShipmentService.createShipment({
                order_id: shipmentData.item.order.id,
                order_version_id: shipmentData.item.order.versionId,
                shipping_option_id: shipmentData.item.id,
            })
                .then((response) => {
                    if (
                        response.success
                        && !!response.shipment
                    ) {
                        let order = {
                            order_id: shipmentData.item.order.id,
                            order_version_id: shipmentData.item.order.versionId
                        };

                        this.createConsignments([order], shipmentData, response.shipment);
                    }
                });

            this.createSingleShipment.showModal = false;
        },

        createConsignments(orderIds, shipmentData, shipment) {
            this.MyParcelConsignmentService.createConsignments({
                order_ids: orderIds,
                label_positions: shipmentData.printPosition,
                package_type: shipmentData.packageType,
                number_of_labels: shipmentData.numberOfLabels,
                shipment_id: shipment.id
            })
                .then((response) => {
                    // shipmentData.actionType
                    console.log(response);
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

        onOpenCreateSingleShipmentModal(item) {
            this.createSingleShipment.item = item;
            this.selectedShippingOptionIds = [item.id];
            this.createSingleShipment.showModal = true;
        },

        onCloseSingleShipmentModal() {
            this.createSingleShipment.showModal = false;
        },

        onCreateSingleShipment() {
            if (!!this.createSingleShipment.item) {
                this.shippingOptionRepository.save(this.createSingleShipment.item, Shopware.Context.api)
                    .then(() => {
                        this.saveSingleShipment(this.createSingleShipment);
                    })
                    .catch(() => {
                        //
                    });
            }
        },

        onOpenCreateMultipleShipmentsModal() {
            if (
                !!this.selectedShippingOptionIds
                && this.selectedShippingOptionIds.length
            ) {
                this.createMultipleShipments.items = this.selectedShippingOptions;
                this.createMultipleShipments.showModal = true;
            }
        },

        onCloseMultipleShipmentsModal() {
            this.createMultipleShipments.showModal = false;
        },

        onCreateMultipleShipments() {
            console.log(this.createMultipleShipments.items);
        },
    }
});