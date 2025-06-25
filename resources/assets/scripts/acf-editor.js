acf.add_action('ready', function( $el ){

    /**
     * Bogo Deal Type
     */
    const iDealTypeSame     = document.getElementById( 'acf-bogo_deals_type-same' );
    const iDealsType        = document.querySelectorAll( 'input[type="radio"][name="acf[bogo_deals_type]"]' );
    const iMultipleMost     = document.getElementById( 'acf-bogo_deals_multiple-most' );
    const iMultipleLess     = document.getElementById( 'acf-bogo_deals_multiple-less' );
    const multipleContainer = document.querySelector( '.acf-field-bogo-deals-multiple' );


    if ( iDealTypeSame.checked ) {
        iMultipleMost.setAttribute("disabled","disabled");
        iMultipleLess.setAttribute("disabled","disabled");
        multipleContainer.style.opacity = "0.5";
    }


    iDealsType.forEach( ( iDealType ) => {
        iDealType.addEventListener( "change", ( event ) => {
            
            if ( event.target.value === 'same' ) {
                iMultipleMost.setAttribute("disabled","disabled");
                iMultipleLess.setAttribute("disabled","disabled");
                multipleContainer.style.opacity = "0.5";
            } else {
                iMultipleMost.removeAttribute("disabled");
                iMultipleLess.removeAttribute("disabled");
                multipleContainer.style.opacity = "1";
            }

        } );
    } );
    

    /**
     * Bogo Deal Ulogged
     */
    const iuLogged       = document.getElementById( 'acf-bogo_deals_ulogged' );
    const uroleContainer = document.querySelector( '.acf-field-bogo-deals-urole' );

    if ( ! iuLogged.checked ) {
        uroleContainer.style.opacity = "0.5";
    }

    $el.on('change', '#acf-bogo_deals_ulogged', ( event ) => {

        if( event.target.checked ) {
            uroleContainer.style.opacity = "1";
        } else {
           uroleContainer.style.opacity = "0.5";
        }
    });


    /**
     * Bogo Deal Notices
     */
    const iNoticesProduct = document.getElementById( 'acf-bogo_deals_notices_product' );
    const iNoticesProductImg = document.querySelector( '.acf-field-bogo-deals-notices-product img' );

    if ( ! iNoticesProduct.checked ) {
        iNoticesProductImg.style.opacity = "0.5";
    }

    $el.on('change', '#acf-bogo_deals_notices_product', ( event ) => {

        if( event.target.checked ) {
            iNoticesProductImg.style.opacity = "1";
        } else {
            iNoticesProductImg.style.opacity = "0.5";
        }
    });


    const iNoticesShop    = document.getElementById( 'acf-bogo_deals_notices_shop' );
    const iNoticesShopImg = document.querySelector( '.acf-field-bogo-deals-notices-shop img' );

    if ( ! iNoticesShop.checked ) {
        iNoticesShopImg.style.opacity = "0.5";
    }

    $el.on('change', '#acf-bogo_deals_notices_shop', ( event ) => {

        if( event.target.checked ) {
            iNoticesShopImg.style.opacity = "1";
        } else {
            iNoticesShopImg.style.opacity = "0.5";
        }
    });
});