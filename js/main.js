//Call to update rates in background
$(function() {
  $.post("ajax.php", {
      action: "update",
      csrf_token: csrf
    });
});

// Change the amount entered
$("#amount").change(function(){
    calculate();
});

// Change currency
$(".dropdown-menu li a").click(function(event){
  event.preventDefault();
  var selText = $(this).text();
  var parent = $(this).parents('.input-group-btn').find('.dropdown-toggle');

  parent.html(selText+' <span class="caret"></span>');
  parent.data("value", selText);
  calculate();
});

// Get value
function calculate() {
  if ($.isNumeric($("#amount").val())) {
    $('#message').html();
    $.post("ajax.php", {
        action: "quote",
        source: $('#source_currency').data('value'),
        target: $('#target_currency').data('value'),
        amout: $("#amount").val(),
        csrf_token: csrf
      },
      function(result){
        $('#total').val(result);
      }
    );
  } else {
    $('#message').html("Enter a valid number in the amount.");
  }
}
