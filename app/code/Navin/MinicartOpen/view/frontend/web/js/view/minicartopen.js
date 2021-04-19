define(["jquery/ui","jquery"], function(Component, $){
    return function(config, element){
        var minicart = $(element);
        minicart.on('contentLoading', function () {
            minicart.on('contentUpdated', function () {
                minicart.find('[data-role="dropdownDialog"]').dropdownDialog("open");
                //adding cart count in localstorage
                console.log('mini cart updated')
                var count = parseInt(jQuery('.counter-number').text());
                if(count >= 0){
                    localStorage.setItem("cartvalue", count);
                }
            });
        });
    }
});