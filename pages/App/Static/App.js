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
})(window);