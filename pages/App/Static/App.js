(function(win){
    // set visibility
    var title = document.title, switched = null, text = [
        "Don't leave now, there is so much to explore.",
        title
    ];

    win.addEventListener('blur', function(){
        
        var index = 0;

        switched = setInterval(function(){
            if (index == text.length)
            {
                index = 0;
            }
            document.title = text[index];
            index++;
        },2000);

    }, false);

    win.addEventListener('focus', function(){
        document.title = title;
        if (switched !== null)
        {
            clearInterval(switched);
        }
    }, false);

    win.addEventListener('load', function(){
        // get preloader
        var preloader = document.querySelector('.preloader');
        if (preloader !== null)
        {
            var preloaderimg = preloader.querySelector('img');

            preloaderimg.style.opacity = 0;

            setTimeout(() => {
                preloader.classList.add('moveOut');

                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 1500);
            }, 800);
        }
    });

    $(document).ready(function(){
         // manage dropdown
        var mean = $('.mean-expand');
        mean.on('click', function(){
            var parent = this.parentNode.querySelector('.mobile-dropdown');
            if (parent.hasAttribute('data-clicked'))
            {
                parent.style.display = 'none';
                parent.removeAttribute('data-clicked');
            }
            else
            {
                parent.style.display = 'block';
                parent.setAttribute('data-clicked', true);
            }
        });

        $('.dropdown-trigger2').click(function(){
            var expand = this.parentNode.querySelector('.mean-expand');
            expand.click();
        });

        
    });

})(window);