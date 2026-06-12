(function ($, Drupal) {
  const $win = $(window);
  Drupal.behaviors.showprovider = {
    attach: function (context, settings) { // eslint-disable-line no-unused-vars, object-shorthand
// .once('showprovider')
      $( ".show-provider-details" ).on( "click", function() {
        $(this).addClass('show-it')
        $(this).removeClass('show-provider-details')
      });
    },
  };
}(jQuery, Drupal, document));


