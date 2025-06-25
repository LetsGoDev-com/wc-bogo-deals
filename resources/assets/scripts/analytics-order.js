let bogoDealItems = [
	{
        value: 'all',
        label: 'All',
    },
];


Object.keys(bogo_deals.items).forEach( function( id ) {
	bogoDealItems.push( { value: id, label: bogo_deals.items[id] } );
});


wp && wp.hooks && wp.hooks.addFilter('woocommerce_admin_orders_report_advanced_filters', 'add-advanced-filter', function (advancedFilters) {
    advancedFilters.filters['bogo_deals'] = {
    	allowMultiple: true,
        labels: {
            add: wp.i18n.__( 'Bogo Deals', 'woocommerce' ),
            remove: wp.i18n.__('Remove Bogo Deals filter', 'woocommerce' ),
            rule: wp.i18n.__( 'Select Bogo Deals filter', 'woocommerce' ),
            title: wp.i18n.__( '<title>Orders by Deal</title> <rule/> <filter/>', 'woocommerce' ),
            filter: wp.i18n.__( 'Select bogo deals orders to show', 'woocommerce' )
        },
        rules: [
			{
				value: 'is',
				/* translators: Sentence fragment, logical, "Is" refers to searching for products matching a chosen attribute. Screenshot for context: https://cloudup.com/cSsUY9VeCVJ */
				label: wp.i18n._x( 'Is', 'product attribute', 'woocommerce' ),
			},
			{
				value: 'is_not',
				/* translators: Sentence fragment, logical, "Is Not" refers to searching for products that don\'t match a chosen attribute. Screenshot for context: https://cloudup.com/cSsUY9VeCVJ */
				label: wp.i18n._x( 'Is Not', 'product attribute', 'woocommerce' ),
			},
		],
        input: {
            component: 'SelectControl',
            options: bogoDealItems,
            defaultOption: 'all'
        }
    };

    return advancedFilters;
});