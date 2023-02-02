var sparklineLogin = function() { 
    $('#sales1').sparkline([20, 40, 30], {
        type: 'pie',
        height: '90',
        resize: true,
        sliceColors: ['#01c0c8', '#7d5ab6', '#ffffff']
    });
    $('#sparkline2dash').sparkline([6, 10, 9, 11, 9, 10, 12], {
        type: 'bar',
        height: '154',
        barWidth: '4',
        resize: true,
        barSpacing: '10',
        barColor: '#25a6f7'
    });
    
};    
var sparkResize;

$(window).resize(function(e) {
    clearTimeout(sparkResize);
    sparkResize = setTimeout(sparklineLogin, 500);
});
sparklineLogin();

$(function () {
    $(".select2").select2();
    $(".user-search").select2({
        ajax: {
            url: '/api/clients',
            dataType: 'json',
        }
    });

    $('#toggle-menu').on('click', function(){
        $('#main-wrapper').toggleClass('hidden-sidebar');
    });

    onlyNumbers = function( ele ){
        if( ele.length == 1 ){
            val = ele.val();
            ele.val( val.replace(/[^0-9]/g, "") );
        }
    }

    onlyNumbers( $('.only-numbers') )
    $('.only-numbers').on('change', function(){
        onlyNumbers( $(this) )
    });

    $('.only-numbers').on('keyup', function(){
        onlyNumbers( $(this) )
    });

    

});
