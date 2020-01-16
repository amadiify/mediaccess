// manage listing
(function(){
    var wishlist = document.querySelectorAll('*[data-wishlist]');
    if (wishlist.length > 0)
    {
        var http;

		if(window.ActiveXObject)
		{
			http = new ActiveXObject("XMLHTTP.Microsoft");
		}
		else
		{
			http = new XMLHttpRequest();
        }
        

        [].forEach.call(wishlist, function(e){
            e.addEventListener('click', function(ev){
                var type = e.getAttribute('data-wishlist');
                if (type != 'oauth')
                {
                    ev.preventDefault();
                    var newLink = '';
                    if (type == 'false')
                    {
                        newLink = e.href.replace('remove', 'add');
                        if (e.hasAttribute('data-wishlist-text'))
                        {
                            e.querySelector('b').innerText = 'Add to Wishlist';
                            var total = e.querySelector('.total-wishlist');
                            if (total != null)
                            {
                                var digit = Number(total.innerText.match(/(\d)+/)[0]);
                                total.innerText = '('+(digit-1)+')';
                            }
                        }
                    }
                    else
                    {
                        newLink = e.href.replace('add', 'remove');
                        
                        if (e.hasAttribute('data-wishlist-text'))
                        {
                            e.querySelector('b').innerText = 'Remove from Wishlist';
                            var total = e.querySelector('.total-wishlist');
                            if (total != null)
                            {
                                var digit = Number(total.innerText.match(/(\d)+/)[0]);
                                total.innerText = '('+(digit+1)+')';
                            }
                        }
                    }
                    type = type == 'false' ? 'true' : 'false';
                    e.setAttribute('data-wishlist', type);
                    var href = e.href;
                    http.open('GET', href, true);
                    http.send();
                    e.href = newLink;
                }
            });
        });
    }
})();