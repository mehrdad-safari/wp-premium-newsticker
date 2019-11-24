jQuery( document ).ready( function ( $ ) {

if($(".wp-premium-newsticker").length)
$(".wp-premium-newsticker").each(function(){
         $(this).find(".premium-newsticker-type").typed({
            stringsElement:  $(this).find('.strings-to-type');
        });
});

});