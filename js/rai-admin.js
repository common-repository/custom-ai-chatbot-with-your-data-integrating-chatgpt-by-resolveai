(function ($)
{
    function copyToClipboard(element, elementSucccess = null) {
      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val($(element).text()).select();
      document.execCommand("copy");
      $temp.remove();

      if(elementSucccess) {
        $(elementSucccess).show();
        setTimeout(function () {
           $(elementSucccess).hide();
        }, 2000);
      }
    }

    $(document).ready(function ()
    {        
        $('#add-new-url').click(function(e) {
            e.preventDefault();
            let new_url = $(".urls-wrapper .new-url").first().clone(); 
            new_url.find('input').val('');
            new_url.find('select').val('show');
            $(".urls-wrapper").append(new_url);
        });

        $("#copy-shortcode").click(function (e) {
          e.preventDefault();
          copyToClipboard("#shortcode", "#copy-success");
        });
    });
})(jQuery);