const { join, resolve } = require('path');
module.exports = () => {
    return {
        resolve: {
            alias: {
                '@myparcel': resolve(
                    join(__dirname, '..', 'node_modules', '@myparcel/delivery-options')
                )
            }
        }
    };
}
