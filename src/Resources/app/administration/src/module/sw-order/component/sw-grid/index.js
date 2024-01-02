import template from './naampje.html.twig';
Shopware.Component.override('sw-order-list-grid-columns', {
    template,
});

console.warn('hallo, ik wil de order list grid columns extenden');
