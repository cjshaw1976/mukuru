//Call to update rates in background
$(function() {
  $( "#amount" ).focus();
  $.post("ajax.php", {
      action: "update",
      csrf_token: csrf
    });
});

// Change the amount entered
$("#amount").change(function(){
    calculate();
});
$("#amount").keyup(function(){
    calculate();
});

// Swop click
$("#swop").click(function(){
  var temp_value = $('#source_currency').data('value');
  $('#source_currency').data('value', $('#target_currency').data('value'));
  $('#source_currency').html($('#target_currency').data('value') + ' <span class="caret"></span>');
  $('#target_currency').data('value', temp_value);
  $('#target_currency').html(temp_value + ' <span class="caret"></span>');
  calculate()
  $( "#amount" ).focus();
});

// Purchase click
$("#purchase").click(function(){
  // Check amount compleated
  var amount = $("#amount").val();
  if (!$.isNumeric(amount) || amount.trim() == '') {
    $('#message').html("Enter a valid number in the amount to purchase.");
    return;
  }

  // Purchase
  $.post("ajax.php", {
      action: "purchase",
      source: $('#source_currency').data('value'),
      target: $('#target_currency').data('value'),
      amount: $("#amount").val(),
      csrf_token: csrf
    },
    function(result){
      $('#ajax').text(result);
      obj = JSON.parse(result)
      $('#total').val(obj.total);
      $('#message').html('You have purchased ' + obj.currency + ': ' + obj.foreign + ', for USD: ' + obj.usd + '. Reference: ' + obj.purchace_id);

      //Add to display table:
      $('<tr><td>' + obj.purchace_id + '</td><td>' + obj.currency + '</td><td>' + obj.exchange_rate + '</td><td>' + obj.surcharge_percentage + '</td><td>' + obj.foreign + '</td><td>' + obj.usd + '</td><td>' + obj.surcharge_amount + '</td><td>' + obj.timestamp + '</td><td>' + obj.discount_amount + '</td></tr>').hide().prependTo('table > tbody').fadeIn("slow");

      // Empy Fields
      $('#total').val('');
      $('#amount').val('');
      $( "#amount" ).focus();
    }
  );

});

// Change currency
$(".dropdown-menu li a").click(function(event){
  event.preventDefault();
  var selText = $(this).text();
  var parent = $(this).parents('.input-group-btn').find('.dropdown-toggle');
  var last = parent.data("value");

  // Make sure both wont be USD
  if (selText == 'USD'){
    if ((parent.attr('id') == 'target_currency' && $('#source_currency').data('value') == 'USD') ||
        parent.attr('id') == 'source_currency' && $('#target_currency').data('value') == 'USD') {
          selText = last;
    }
  }

  // Display selected
  parent.html(selText+' <span class="caret"></span>');
  parent.data("value", selText);


  // Make sure that either source or target are USD
  if ($('#target_currency').data('value') != 'USD' && $('#source_currency').data('value') != 'USD'){
    if (parent.attr('id') == 'target_currency') {
      $('#source_currency').html('USD <span class="caret"></span>');
      $('#source_currency').data("value", 'USD');
    } else {
      $('#target_currency').html('USD <span class="caret"></span>');
      $('#target_currency').data("value", 'USD');
    }
  }

  calculate();
  $( "#amount" ).focus();
});

// Get quote value
function calculate() {
  var amount = $("#amount").val();
  if (amount.trim() == '') { amount = 0;}
  if ($.isNumeric(amount)) {
    $('#message').html('&nbsp');
    $.post("ajax.php", {
        action: "quote",
        source: $('#source_currency').data('value'),
        target: $('#target_currency').data('value'),
        amount: $("#amount").val(),
        csrf_token: csrf
      },
      function(result){
        $('#ajax').text(result);
        obj = JSON.parse(result)
        $('#total').val(obj.total);
      }
    );
  } else {
    $('#message').html("Enter a valid number in the amount to purchase.");
    $('#total').val('');
  }
}
