$(document).ready(function() {
   var interval;
   var url = $('.start').attr('url');

   $('.global-actions').append('<div id="timer"></div>')

   $('.start').on('click', function(evt) {
      evt.preventDefault();
      if( $.trim($(this).text()) === 'start') {
         $(this).text('stop');
         var elapsed_seconds = 0;
         interval = setInterval(function() {
            elapsed_seconds = elapsed_seconds + 1;
            $('#timer').text(get_elapsed_time_string(elapsed_seconds));
         }, 1000);
         doAjax(url,'start', '');
      } else {
         doAjax(url,'stop', $('#timer').text());
         $(this).text('start');
         clearInterval(interval);
         $('#timer').text('00:00:00');
      }

   });
});

function get_elapsed_time_string(total_seconds) {
   function pretty_time_string(num) {
      return ( num < 10 ? "0" : "" ) + num;
   }

   var hours = Math.floor(total_seconds / 3600);
   total_seconds = total_seconds % 3600;

   var minutes = Math.floor(total_seconds / 60);
   total_seconds = total_seconds % 60;

   var seconds = Math.floor(total_seconds);

   hours = pretty_time_string(hours);
   minutes = pretty_time_string(minutes);
   seconds = pretty_time_string(seconds);

   var currentTimeString = hours + ":" + minutes + ":" + seconds;

   return currentTimeString;
}

function doAjax(url, startOrStop, time) {
   $.ajax({
      type: 'POST',
      url: url,
      data: {'action': startOrStop, 'time': time},
      success: function()
      {
         if(startOrStop === 'stop') {
            window.location.reload();
         }
      }
   })
}

