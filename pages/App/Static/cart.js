var circleLoaderClass = {
    loaderElement : null,
    showLoader : function(){
        circleLoaderClass.loaderElement = document.querySelector('.circle-loader-wrapper');
        circleLoaderClass.loaderElement.style.display = 'flex';
        setTimeout(function(){
            circleLoaderClass.loaderElement.style.opacity = '1';
        },100);
    },
    hideLoader : function(){
        circleLoaderClass.loaderElement.style.opacity = '0';
        setTimeout(function(){
            circleLoaderClass.loaderElement.style.display = 'none';
        },600);
    }
};

// get quantity
var qty = document.querySelectorAll('.input-spinner');
if (qty.length > 0)
{
    var subtotal = document.querySelector('*[data-target="subtotal"]'),
    shipping = document.querySelector('*[data-target="shipping"]'),
    total = document.querySelector('*[data-target="total"]');

    function applyQuantity(event)
    {
        circleLoaderClass.showLoader();

        setTimeout(function(){
            var input = event.target.parentNode.querySelector('input');
            var cartid = input.getAttribute('data-cartid');
            var amount = document.querySelector('*[data-amount="'+cartid+'"]');

            $http.get($url + 'cart/quantity?qty=' + input.value + '&cartid=' + cartid, function(res){
                amount.innerText = res.data.price;
                subtotal.innerText = res.data.subtotal;
                shipping.innerText = res.data.shipping;
                total.innerText = res.data.total;
                circleLoaderClass.hideLoader();
            });
        },200);
        
    }
}