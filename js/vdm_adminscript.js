/**
  * Check on save if this post contains a VoordeMensen shortcode but was not connected to an event
  *
  * @fires createNotice
*/
const unsubscribe = wp.data.subscribe(function () {
    let select = wp.data.select('core/editor');
    var isSavingPost = select.isSavingPost();
    var isAutosavingPost = select.isAutosavingPost();
    var didPostSaveRequestSucceed = select.didPostSaveRequestSucceed();
    var EditedPostContent = select.getEditedPostContent();
    if (isSavingPost && !isAutosavingPost && didPostSaveRequestSucceed) {
        if(EditedPostContent.includes('[vdm') && !jQuery('#voordemensen_event_id').val()) {
            unsubscribe();
            jQuery('#voordemensen_event_id').focus();
            ( function( wp ) {
                wp.data.dispatch( 'core/notices' ).createNotice(
                    'error',
                    'Let op: je gebruikt een VoordeMensen-shortcode terwijl deze post niet aan een VoordeMensen Evenement is gekoppeld!', 
                    {
                        isDismissible: true,
                        type: 'snackbar'
                    }
                );
            } )( window.wp );
        }        
    }
});