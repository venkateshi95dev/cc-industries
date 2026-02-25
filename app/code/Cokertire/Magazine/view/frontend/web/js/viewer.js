jQuery(function($){
    // grab the iframe url parameters
    //var loc = window.location.toString(),
    // params = loc.split('?')[1];
    // catalog_param = params.split('&');

    // // add parameters to create the catalog
    // var app_url = catalog_param[0].split('=')[1];
    // console.log(app_url);
    // var catalog_pages = parseInt(catalog_param[1].split('=')[1]); // insert the number of pages this catalog has
    // var catalog_folder = '/' + catalog_param[2].split('=')[1];
    // var year = parseInt(catalog_param[3].split('=')[1]);
    var app_url = 'https://web.cokertire.com/media/magviewer'
    var catalog_pages = 4;
    var catalog_path =  app_url + '/PDF/Catalog_2024/';

    // set catalog width and height
    var catalog_width = '1022';//parseInt(catalog_param[4].split('=')[1]);
    var catalog_height = '692';//parseInt(catalog_param[5].split('=')[1]);

    var displayit = $('.display_catalog').css('width', catalog_width);
    var button = $('<div class="the_buttons_full">');
    var magdiv = $('<div class="magazine">');
    var pages = $('<ul id="pages">');
    var first = $('<li class="cover_page b-page-cover"><img class="pdf" src="'+ catalog_path +'page0.jpg" style="width:'+ width_split +'px;height:'+ catalog_height +'px" /></li>');

    // start building the magazine
    button.appendTo(displayit);
    magdiv.appendTo(button);
    pages.appendTo(magdiv);
    first.appendTo(pages);

    pages.css('height', catalog_height);
    var width_split = catalog_width / 2,
        blank_page = $('.blank_page img'),
        cover_page = $('.cover_page img'),
        catalog_footer = $('.catalog_footer'),
        footer_pages = $('ul.footer_pages');

    $('.magazine, .catalog_footer, .catalog_footer_placeholder').css('width', catalog_width);

    var pageimgs = '';
    for(var i = 1; i <= catalog_pages; i++){
        pageimgs += ('<li class="sxs"><img class="pdf" src="'+ catalog_path +'page' + i + '.jpg" style="width:'+ width_split +'px;height:'+ catalog_height +'px" /></li>');
    }

    pages.append(pageimgs);

    // add animations to make it more "book-like"
    pages.booklet({
      closed: true,
      autoCenter: true,
      speed: 500,
      height: catalog_height,
      width: catalog_width,
      pagePadding: 0,
      hoverWidth: 100,
      hash: true,
      menu: "#catalog_menu",
      tabs:  true,
      tabWidth:  80,
      tabHeight:  24,
      nextControlText: '<img src="'+ app_url +'/img/right.png">',
      previousControlText: '<img src="'+ app_url +'/img/left.png">',
      pageSelector: true,
      keyboard: true
    });
    $('.b-controls').append('<div class="btn btn-sm btn-success pointer viewfull">view full screen</div>');

    var menu_lis = $('#catalog_menu').find('ul li');
    var pages_clone = menu_lis.clone();
    pages_clone.removeClass('hide').addClass('footer_split');
    $('#catalog_menu').remove();
    var inner_footer = footer_pages.html(menu_lis);

    var thumb_height = parseInt(catalog_height * 0.13),
         thumb_width  = parseInt(catalog_width * 0.13);

    var selectpage = $('ul.footer_pages li a[id^="selector-page-"]');
    var lastnbr = parseInt(catalog_pages + 1);
    selectpage.each(function(){
      var pagenum = $(this).attr('id').split('-');
      var nbr = parseInt(pagenum[2]);
      if(nbr !== 0 && nbr !== lastnbr){
        $(this).append(
          '<img class="pdf" src="'+ catalog_path +'page' + (nbr - 1) + '.jpg" style="width:'+ thumb_width +'px;height:'+ thumb_height +'px" />' +
          '<img class="pdf" src="'+ catalog_path +'page' + nbr + '.jpg" style="width:'+ thumb_width +'px;height:'+ thumb_height +'px" />'
        );
      }
    });
    $('ul.footer_pages li a[id="selector-page-0"]').append('<img class="pdf" src="'+ catalog_path +'page0.jpg" style="width:'+ thumb_width +'px;height:'+ thumb_height +'px" />');
    $('ul.footer_pages li a[id="selector-page-'+ lastnbr +'"]').append('<img class="pdf" src="'+ catalog_path +'page' + (lastnbr - 1) + '.jpg" style="width:'+ thumb_width +'px;height:'+ thumb_height +'px" />');

    footer_pages_resized = inner_footer.css('width', (thumb_width + 20) * catalog_pages);
    footer_pages_resized.find('img.pdf').css({
         'height': 'auto',
         'width': thumb_width
    });
    //
    catalog_footer.html(footer_pages_resized);

    // show catalog thumbs in the footer when hovered over in that area
    catalog_footer.hover(
      function(){
        footer_pages_resized.fadeIn({'duration': 600});
      },
      function(){
        footer_pages_resized.fadeOut({'duration': 600});
    });

    var newHeight = parseInt(screen.height - 30);
    var newWidth = parseInt(newHeight * 1.4768);

    function bookSize(bookheight, bookwidth, btntext){
      pages.booklet({
        closed: true,
        autoCenter: true,
        speed: 500,
        height: bookheight,
        width: bookwidth,
        pagePadding: 0,
        hoverWidth: 100,
        hash: true,
        menu: "#catalog_menu",
        tabs:  true,
        tabWidth:  80,
        tabHeight:  24,
        nextControlText: '<img src="'+ app_url +'/img/right.png">',
        previousControlText: '<img src="'+ app_url +'/img/left.png">',
        pageSelector: true,
        keyboard: true
      });
      (btntext == 'view') ? btntype = 'btn-success' : btntype = 'btn-danger'; // change button color depending on view size
      $('.b-controls').append('<div class="btn btn-sm pointer '+ btntype +' '+ btntext +'full">'+ btntext +' full screen</div>');
      $('.entire_book, .display_catalog, .magazine, .booklet').css({
        'height': bookheight,
        'width': bookwidth
      });
      $('.catalog_footer, .catalog_footer_placeholder').css('width', bookwidth);
      $('ul#pages img.pdf').css({
        'height': bookheight,
        'width': bookwidth / 2
      });
      $('.b-wrap').css('width', bookwidth / 2);

      $('.exitfull').click(function(){
        screenfull.exit();
        location.reload();
        return false;
      });
    }

    // enter full screen
    var getbook = $('.entire_book')[0];
    $('.viewfull').click(function() {
      $('#pages.booklet').booklet('disable');
      if(screenfull.isEnabled) {
        screenfull.request(getbook);
        bookSize(newHeight, newWidth, 'exit');
      }
      return false;
    });
});
