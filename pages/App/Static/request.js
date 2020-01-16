
var selectors = document.querySelectorAll('.selectors > li');

if (selectors.length > 0)
{
    [].forEach.call(selectors, function(e){
        e.addEventListener('click', function(){
            disselect();
            e.className = 'active';
            var target = e.parentNode.getAttribute('data-target');
            document.querySelector('*[name='+target+']').value = e.innerText;
            var h4 = document.querySelector('.live-feed > h4');
            h4.innerText = e.innerText;
            // ₦
            document.querySelector('.live-feed > h1').innerText = '₦' + e.getAttribute('data-amount');
        });
    });

    function disselect()
    {
        [].forEach.call(selectors, function(e){
            e.removeAttribute('class');
            var target = e.parentNode.getAttribute('data-target');
            document.querySelector('*[name='+target+']').value = '';
        });
    }
}