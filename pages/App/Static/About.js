// process review
var review_form = document.querySelector('#review_form');
if (review_form != null)
{
    review_form.addEventListener('submit', function(e){
        // get all data name
        var datanames = review_form.querySelectorAll('*[data-name]');
        if (datanames.length > 0)
        {
            [].forEach.call(datanames, function(ele){
                // get attribute
                var attr = ele.getAttribute('data-name');
                // get review
                var review = review_form.querySelector('#'+attr);
                // stopped here.
                var rateval = review.getAttribute('data-rate-value');
                ele.value = rateval;
            });
        }
    });
}