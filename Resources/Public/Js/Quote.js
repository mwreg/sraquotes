$(document).ready( function(){
  /**
   * The quotes HTML text element
   * @type {jQuery|HTMLElement|*}
   */
  const quoteText = $('#quote-text');

  /**
   * The quotes HTML category element
   * @type {jQuery|HTMLElement|*}
   */
  const quoteCategory = $('#quote-category');

  /**
   * Button: get next quote
   * @type {jQuery|HTMLElement|*}
   */
  const nextBtn = $('#next-quote-btn');

  const nextBtnSpinner = $('#next-quote-btn-spinner');
  const nextBtnText = $('#next-quote-btn-text');

  /**
   * Get a new quote via AJAX request.
   * Replace the content of currently displayed quote with the requests result.
   */
  const getQuote = function()
  {
    // Disable button
    nextBtn.prop("disabled",true);
    nextBtn.css('cursor', 'not-allowed');

    // Hide button text
    nextBtnText.css('display', 'none');

    // Show spinner
    nextBtnSpinner.css('display', 'block');

    $.ajax({
      method: "GET",
      url: "/",
      data:{
        type:2108,
        tx_sraquotes_plstraquotes:{
          action:'ajaxGetQuote',
          controller:'Quote',
        },
      },
    })
      .done(function( resp ) {
        // Convert response to Object
        resp = JSON.parse(resp);
        quoteText.html( resp[Object.keys(resp)[0]].quote );
        quoteCategory.html( resp[Object.keys(resp)[0]].category );
      })
      .fail(function(e) {
        console.log( e );
      })
      .always(function(){
        // Deactivate button
        nextBtn.prop("disabled",false);
        nextBtn.css('cursor', 'pointer');

        // Hide button text
        nextBtnText.css('display', 'block');

        // Show spinner
        nextBtnSpinner.css('display', 'none');
      })
  }

  /**
   * Add a click handler to the "get next quote button"
   */
  nextBtn.click( function() {
    getQuote();
  });

});
