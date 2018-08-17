// assets/js/app.js

require('../css/main.css');
require('../css/proposer.css');
require('../css/offer.css');
require('../js/jquery.collection.js');

// require jQuery normally
const $ = require('jquery');

// create global $ and jQuery variables
global.$ = global.jQuery = $;

$.fn.visible = function(partial) {

    let $t            = $(this),
        $w            = $(window),
        viewTop       = $w.scrollTop(),
        viewBottom    = viewTop + $w.height(),
        _top          = $t.offset().top,
        _bottom       = _top + $t.height(),
        compareTop    = partial === true ? _bottom : _top,
        compareBottom = partial === true ? _top : _bottom;

    return ((compareBottom <= viewBottom) && (compareTop >= viewTop));

};

function detectmob() {
    if( navigator.userAgent.match(/Android/i)
        || navigator.userAgent.match(/webOS/i)
        || navigator.userAgent.match(/iPhone/i)
        || navigator.userAgent.match(/iPad/i)
        || navigator.userAgent.match(/iPod/i)
        || navigator.userAgent.match(/BlackBerry/i)
        || navigator.userAgent.match(/Windows Phone/i)
    ){
        return true;
    }
    else {
        return false;
    }
}

$(document).ready(function() {
    $('#appbundle_proposer_type').on('change', function(e) {
        if($(this).val() == 'type.house'){
            $('.house-field').show();
            $('.flat-field').hide();
        }else if($(this).val() == 'type.flat'){
            $('.flat-field').show();
            $('.house-field').hide();
        }
    });

    $('.my-selector').collection({
        add: '<a href="#" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign"></span></a><img id="blah" src="#" alt="your image" />',
        remove: '<a href="#" class="btn btn-default"><span class="glyphicon glyphicon-trash"></span></a>',
        allow_up: true,
        allow_down: true
    });

    $(function() {
        $(".meter > span").each(function() {
            $(this)
                .data("origWidth", $(this).width())
                .width(0)
                .animate({
                    width: $(this).data("origWidth")
                }, 1200);
        });
    });

    $('[data-toggle="tooltip"]').tooltip();



    // Select 2 everywhere + placeholder and bootsrap theme
    $('.select2').each(function () {
            $(this).select2({
                width: '100%',
                theme: 'bootstrap',
                placeholder: $(this).data('placeholder')
            });
        if($(this).parent().find('.select2-selection__choice').length > 0){
            $(this).addClass('not-empty');
        }else{
            $(this).addClass('empty');
        }
    });

    $('.select2').on("change", function(e) {
        if (!$(this).val().length) {
            $(this).addClass('empty');
            $(this).removeClass('not-empty');
            $('.next').prop("disabled", true);
        } else {
            $(this).addClass('not-empty');
            $(this).removeClass('empty');
            $('.next').prop("disabled", false);
        }
    });


    //main page

    if(detectmob() == false ){
        $( '.fade-in-right' ).each(function(  ) {
            if( $(this).visible(true)){
                $(this).addClass('fadeInRight');
            }
        });
        $( '.fade-in-left' ).each(function(  ) {
            if( $(this).visible(true)){
                $(this).addClass('fadeInLeft');
            }
        });


        $(window).scroll(function() {
            $( '.fade-in-right' ).each(function(  ) {
                if( $(this).visible(true)){
                    $(this).addClass('fadeInRight');
                }
            });
            $( '.fade-in-left' ).each(function(  ) {
                if( $(this).visible(true)){
                    $(this).addClass('fadeInLeft');
                }
            });
        });
    }

    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 4000);

});
